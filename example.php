<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// include the class
require_once("CurlQueue.class.php");

// define the callback functions
function mycallback($output,$info)
{
	$url = $info["url"];
	echo "Received output from $url.<br />";
	var_dump($info);
	echo "<br /><br />";
}

function anothercallback($output,$info)
{
	$url = $info["url"];
	echo "Custom callbacks can be set for each request. Here's an example. Received output from $url<br />";
	var_dump($info);
	echo "<br /><br />";
}

class foo
{
	public function classcallback($output,$info)
	{
		$url = $info["url"];
		echo "Callbacks can be inside classes. Here's an example. Received output from $url<br />";
		var_dump($info);
		echo "<br /><br />";
	}
}
$foo = new foo();

$curl = new CurlQueue;

$config = array(
	"window" => 5,
	"timeout" => 10,
	"callback" => "mycallback"
);
$curl->config($config);

// HTTPS hosts
$curl->get("https://www.google.com");
$curl->get("http://www.yahoo.com");
$curl->get("http://www.msn.com");
$curl->get("http://www.microsoft.com");
$curl->get("http://www.espn.com");
// POST instead of GET
$curl->post("http://www.cnn.com");
$curl->get("http://www.npr.org");
$curl->get("http://www.nbc.com");
$curl->get("http://www.twitter.com");
$curl->get("http://www.reddit.com");
$curl->get("http://www.metafilter.com");
$curl->get("http://www.netflix.com");
$curl->get("http://www.nytimes.com");
// custom callback
$curl->get("http://www.slashdot.org",NULL,NULL,"anothercallback");
// callback in a class
$curl->get("http://www.news.google.com",NULL,NULL,array($foo,"classcallback"));

if ( $result = $curl->execute() ) echo "All requests have been processed.";
else echo "An error occured that prevented processing of all requests.";

echo "<br /><br />";

echo "If only one request is submitted, a callback function is not necessary. Execute will return the curl output instead of the normal status boolean.<br />";
echo var_dump($result);
?>