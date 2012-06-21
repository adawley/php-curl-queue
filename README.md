PHP Curl Queue Class
====================

A queue based curl wrapping class. Processes curl GET and POST requests from a queue in batches. Output is handled by callback functions which can be set globally for all requests or individually for each request.

Example usage can be found in example.php.

Features
--------

- Efficient queue based processing of HTTP requests.
- Easy to use interface.
- Customizeable window size defines number of requests that are processed in parallel at any one time.
- Ability to define per request or global output callback functions.
- Ability to customize the curl options for each request.
- Ability to choose either GET or POST for each request.


Configurable Options
--------------------

Config values can be set using the config($values) where $values is an array in the form of ````$values = array($key => $value)````.

- ```window``` sets how many requests will be processed at the same time
- ```timeout``` sets the maximum timeout in seconds
- ```options``` is an array of additional default CURLOPT ```$key => $value``` pairs pass to each Curl request
- ```callback``` specifies the default function to call when a request finishes, must be of type function($output,$info)

Adding a Request
----------------

CurlQueue offers 3 ways to add new requests to the waiting queue. In all cases, the only required parameter is ```$url```. If you have not defined a default callback function via the config() function then a callback must be included with each request.

- ```get($url,$headers,$options,$callback)```
- ```post($url,$post_data,$headers,$options,$callback)```
- ```request($url,$method,$post_data,$headers,$options,$callback)```

Parameters
----------

Depending on the type of request being created, there are 4-6 parameters available to further customize the request.

- ```$url``` is a string that contains the URL that should be queried.
- ```$method``` is a string that specifies what type of request this should be - 'GET' or 'POST'.
- ```$post_data``` contains the CURLOPT_POSTFIELDS formatted data to be sent to the remote host.
- ```$headers``` is an array of CURLOPT_HTTPHEADER formatted header fields to be sent to the remote host.
- ```$options``` is an array of additional CURLOPT ```$key => $value``` pairs to further customize each request.
- ```$callback``` is the function to be called, when data has been returned, in the PHP callable format. See below for more details.

Callback Functions
------------------

CurlQueue returns data from each request through a user defined callback function. A default callback function can be set via the ```config()``` function or a callback can be included with each request. Callbacks included with a request overrite the default callback, if one exists.

Functions must be formatted according to the PHP callable type specifications (http://php.net/manual/en/language.types.callable.php). Typically, callback functions will be formatted in one of the following ways.

- Static Function: ```$callback = "myfunctionname"```
- Static Class Method: ```$callback = array("myclass","method_name_in_myclass")```
- Static Class Method: ```$callback = "myclass::method_name_in_myclass"```
- Object Method: ```$callback = array($myclass,"method_name_in_myclass")```

The provided callback must have two parameters - ```$output``` and ```$info```, in that order. ```$output``` contains the raw output from the curl request and ```$info``` contains an array of information about the request from Curl.

Please note that a callback is optional when only a single request has been added. The ```$output``` will be returned via the ```execute()``` function in this case.

Running Requests
----------------

Once all requests have been created and are ready to be executed, you should call the ```execute()``` method. This method will return one of the following.

- ```TRUE``` returned when all requests have been processed succedfully.
- ```FALSE``` returned when all requests have NOT been processed, possibly because of bad config values.
- ```$output``` is the raw output from a single request. This will only be returned when a single request has been added.

Legal
-----

Copyright (c) 2011, Matt Colf

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.

