<?php
// Sets a 2 hour execution time limit
set_time_limit(7200);

// Access the plugin config
$configs = include('config.php');

// Access the wordpress database
if ($configs["isDevelopment"]) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
}
else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
}

global $wpdb;

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Grab all group URL's from database.
$table_name = $wpdb->prefix . "gr_fbgroups";
$urls = $wpdb->get_results("SELECT * FROM $table_name");

// Keep all cron-job activity logged within the log variable
$log = [];

/*foreach ($urls as $i => $url) {
    if ($i+1 === count($urls)) {
        $log .= $url->url;
    }
    else {
        $log .= $url->url + ", ";
    }
}*/

foreach ($urls as $i => $url) {
    // Try to retrieve a page from facebook multiple times. If all tries fail, move on.
    $attempts = 6;
    $success = false;
    while ($attempts > 0 && !$success) {
        $response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $url->url], ['timeout' => 1728000]);
        if ($response->status_code === 200 && $response->body !== "false") {
            $log = $response;
            $success = true;
        }
        $attempts--;
    }
    if (!$success) {
        $log = "No more attempts left to try.";
    }
}

// Send each url to the scraper
//error_log(implode(", ", $urls));
// 

echo json_encode($log);

/*error_log($_POST['delete']);

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}*/
?>