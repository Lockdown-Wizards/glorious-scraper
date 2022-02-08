<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
global $wpdb;

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '\\glorious-scraper\\\requests\\src\\Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Now let's make a request!
//$request = WpOrg\Requests\Requests::get('http://httpbin.org/get', ['Accept' => 'application/json']);
$url = $_POST['url'];
$request = WpOrg\Requests\Requests::get($url, ['Accept' => 'application/json']);

// Check what we received
//var_dump($request);
echo json_encode($request);
?>