<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
//global $wpdb;

// Load the Event class
require_once(__DIR__ . '/Event.php');

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '\\glorious-scraper\\\requests\\src\\Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Grab all URL's from database.
$table_name = $wpdb->prefix . "gr_scraper_urls";
$urls = $wpdb->get_results("SELECT * FROM $table_name");

// If there are any urls that have been entered, then make a request.
$request = "No URLs to scrape from $table_name!";
if ($urls[0] !== null) {
    $request = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $urls[0]->url]);
}

echo json_encode($request);


// get_site_url() . "/wp-content/plugins/glorious-scraper/url-feeder.php";
//echo json_encode($request);

?>