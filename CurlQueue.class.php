<?php

/* CurlQueue
 *
 * A queue based curl wrapper class. Processes curl requests in batches
 * as defined by the window size.
 *
*/

class CurlQueue {

	// master curl handle
	private $master;
	// queue of requests waiting to be processed
	private $queue;
	// map of requests currently being processed
	private $processing;
	// number of requests to process at once
	private $window;
	// max timeout
	private $timeout;
	// default callback function
	private $callback;
	private $callback_count;
	// curl options
	private $options;

	public function __construct()
	{
		$this->queue 			= new ArrayQueue();
		$this->processing 		= new ArrayMap();
		$this->master 			= curl_multi_init();
		$this->set_defaults();
		$this->set_options();
	}

	public function __destruct()
	{
		curl_multi_close($this->master);
	}

	public function config($config = array())
	{
		foreach ( $config as $key => $value )
		{
			switch ($key)
			{
				case "window":
				case "timeout":
				case "options":
				case "callback":				
					$this->{$key} = $value;
					break;
				default:
					return FALSE;
			}
		}
		$this->set_options();
		return TRUE;
	}

	public function request($url, $method = "GET", $post_data = NULL, $headers = NULL, $options = NULL, $callback = NULL)
	{
		if ( $callback ) $this->callback_count++;
		$request = new CurlQueueRequest($url,$method,$post_data,$headers,$options,$callback);
		$this->queue->add($request);
	}

	public function get($url, $headers = NULL, $options = NULL, $callback = NULL)
	{
		$this->request($url,"GET",NULL,$headers,$options,$callback);
	}

	public function post($url, $post_data = NULL, $headers = NULL, $options = NULL, $callback = NULL)
	{
		$this->request($url,"POST",$post_data,$headers,$options,$callback);
	}

	public function execute()
	{
		if ( $this->preflight() )
		{
			if ( $this->queue->size() == 1 ) return $this->process_one();
			else return $this->process_queue();
		}
		else return FALSE;	
	}

	// process a single request
	private function process_one()
	{
		$request 		= $this->queue->next();
		$handle 			= $this->get_handle($request);
		$output 			= curl_exec($handle);
		$info 			= curl_getinfo($handle);

		// run callback
		$this->callback($request,$output,$info);
		
		return $output;
	}

	// process all queued requests
	private function process_queue()
	{
		// start batch
		while ($this->processing->size() < $this->window)
		{
			$request 	= $this->queue->next();
			$handle 		= $this->get_handle($request);

			$this->processing->add((string)$handle,$request);
		}

		// start processing
		do {
			// check for completed request
			while ( ($status = curl_multi_exec($this->master,$running)) == CURLM_CALL_MULTI_PERFORM );

			// check for error
			if ( $status != CURLM_OK ) break;

			// get all completed requests
			while ( $done = curl_multi_info_read($this->master) )
			{
				$request 	= $this->processing->get((string)$done["handle"]);
				$output 		= curl_multi_getcontent($done["handle"]);
				$info 			= curl_getinfo($done["handle"]);

				// run callback
				$this->callback($request,$output,$info);

				// add new request
				if ( $request = $this->queue->next() )
				{
					$handle = $this->get_handle($request);
					$this->processing->add((string)$handle,$request);
				}
				
				// remove completed request
				curl_multi_remove_handle($this->master,$done['handle']);

			}

			// wait for activity
			if ( $running ) curl_multi_select($this->master,$this->timeout);

		} while( $running );
		
		return TRUE;
	}

	// check all settings before processing
	private function preflight()
	{
		
		// NON-CRITICAL
		// the window size must be at least one
		if ( $this->window < 1 ) $this->window = 10;
		// if queue size is greater than one, window must be greater than one
		if ( $this->queue->size() > 1 AND $this->window < 2 ) $this->window = min($this->queue->size(),10);
		// window cannot be larger than queue size
		if ( $this->queue->size() < $this->window ) $this->window = $this->queue->size();
		// CRITICAL
		// there must be requests in the queue
		if ( $this->queue->size() == 0 ) return FALSE;
		// if a callback is not set, each request must have one
		if ( $this->callback == NULL AND $this->callback_count < $this->queue->size() ) return FALSE;		

		return TRUE;
	}

	// get a curl handle for a request
	private function get_handle($request)
	{
		$handle = curl_init();

		curl_setopt_array($handle,$this->build_options($request));
		curl_multi_add_handle($this->master,$handle);

		return $handle;
	}

	// run user callback function
	private function callback($request,$output,$info)
	{
		if ( $request->callback ) $callback = $request->callback;
		else $callback = $this->callback;

		if ( is_callable($callback) ) call_user_func($callback,$output,$info);
	}

	// set class defaults
	private function set_defaults()
	{
		$this->window 				= 10;
		$this->timeout 				= 15;
		$this->callback 				= NULL;
		$this->callback_count			= 0;
	}

	// setup the curl options array
	private function set_options()
	{
		$this->options = array(
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_CONNECTTIMEOUT => $this->timeout,
			CURLOPT_TIMEOUT => $this->timeout
		);
	}

	// build curl options array
	private function build_options($request)
	{
		// start with global options
		$options = $this->options;

		// append custom request options
		if ( $request->options )
		{
			$options = $options + $request->options;
		}		

		$options[CURLOPT_URL] = $request->url;

		// set post data
		if ( $request->post_data OR $request->method == "POST" )
		{
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $request->post_data;
		}

		// set headers
		if ( $request->headers )
		{
			$options[CURLOPT_HEADER] = 0;
			$options[CURLOPT_HTTPHEADER] = $request->headers;
		}

		return $options;
	}
}

class CurlQueueRequest {

	private $url;
	private $method;
	private $post_data;
	private $headers;
	private $options;
	private $callback;
	private $id;

	public function __construct($url, $method = "GET", $post_data = NULL, $headers = NULL, $options = NULL, $callback = NULL)
	{
		$this->url = $url;
		$this->method = $method;
		$this->post_data = $post_data;
		$this->headers = $headers;
		$this->options = $options;
		$this->callback = $callback;
		$this->id = uniqid((string)rand(11,99),TRUE);
	}
	public function __get($key)
	{
		return isset($this->{$key}) ? $this->{$key} : NULL;
	}

	public function __set($key,$value)
	{
		$this->{$key} = $value;
		return TRUE;
	}

}

class ArrayQueue
{
	private $queue;
	private $size;

	public function __construct()
	{
		$this->size = 0;
		$this->queue = array();
	}

	public function is_empty()
	{
		return ($this->size == 0);
	}

	public function size()
	{
		return $this->size;
	}

	public function add($item)
	{
		$this->size = array_push($this->queue,$item);
	}

	public function next()
	{
		return array_shift($this->queue);
	}

}

class ArrayMap
{
	private $map;
	private $size;

	public function __construct()
	{
		$this->size = 0;
		$this->map = array();
	}

	public function is_empty()
	{
		return ($this->size == 0);
	}

	public function size()
	{
		return $this->size;
	}

	public function add($id,$item)
	{
		$this->map[$id] = $item;
		$this->size++;
	}

	public function get($id)
	{
		if ( isset($this->map[$id]) )
		{
			$item = $this->map[$id];
			unset($this->map[$id]);
			$this->size--;
			return $item;
		}
		else return FALSE;
	}
}

?>