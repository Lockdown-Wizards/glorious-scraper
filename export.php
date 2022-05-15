<?php
/*
 * This is the script which generates an export file that can then be used to import events into the site.
*/

// Sets a 2 hour execution time limit
set_time_limit(7200);

// Access the plugin config
$configs = include('config.php');

// Get the name of the folder which wordpress resides in. (Only needed for development builds)
$folder_name = null;
if ($configs["isDevelopment"]) {
    $folder_name = explode('/', explode('/wp-content', str_replace('\\', '/', __DIR__))[0]);
    $folder_name = $folder_name[count($folder_name)-1];
}

// Access the wordpress database
if ($configs["isDevelopment"]) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/' . $folder_name . '/wp-load.php'); // Development
}
else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
}
global $wpdb;

// Load the GroupPage and EventPage classes
require_once(__DIR__ . '/GroupPage.php');
require_once(__DIR__ . '/EventPage.php');

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Grab all group URL's from database.
$table_name = $wpdb->prefix . "gr_fbgroups";
$urls = $wpdb->get_results("SELECT * FROM $table_name");

$group_pages = [];

// Create group page objects for each facebook url stored in the wordpress database
foreach ($urls as $i => $url) {
    $group_pages[] = new GroupPage($url->url);
}

// Scrape each group page.
foreach ($group_pages as $i => $group_page) {
    // Try to retrieve a group page from facebook multiple times. If all tries fail, move on.
    $start_time = time(); // Set up a timer so we can monitor execution times.
    $attempts = intval($configs["maxAttempts"]);
    $success = false;
    $response = null;
    while ($attempts > 0 && !$success) {
        $response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $group_page->get_url()], ['timeout' => 1728000]);
        if ($response->body !== "false") {
            $group_page->set_scrape($response->body);
            $group_page->set_has_scraped(true);
            $group_page->set_execution_time(time() - $start_time);
            //$group_page->set_scrape_status("");
            $success = true;
        }
        $attempts--;
    }
    if (!$success) {
        $group_page->set_scrape($response->body);
        //$group_page->set_has_scraped(false);
        $group_page->set_scrape_status("No more attempts left to try.");
        $group_page->set_execution_time(time() - $start_time);
    }
}

// Create EventPage objects from the results of all successful GroupPage scrapes
foreach ($group_pages as $i => $group_page) {
    $allArgs = json_decode($group_page->get_scrape());
    foreach ($allArgs as $args) {
        $event_page = new EventPage($args->event->EventURL);
        $event_page->set_event($args->event);
        $event_page->set_venue($args->venue);
        $group_page->add_event_page($event_page);
    }
}

// Write the group_page object to a file.
serialize_group_pages_to_json_file($group_pages);


if ($configs["enableCronjobLogger"]) {
    // Write all data obtained during the scrape into a seperate log file within the log folder.
    write_group_pages_to_export_log($group_pages);
    echo json_encode("Export completed.");
}
else {
    // Prepare the results of the scrape for sending back to the front end (for testing purposes)
    $results = [];
    foreach ($group_pages as $group_page) {
        $results[] = $group_page->to_array();
    }
    echo json_encode($results);
}

function serialize_group_pages_to_json_file($group_pages) {
    global $configs;

    // Get time to differentiate log file
    $date = new DateTime('now');
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $date_str = $date->format('Y-m-d_H_i_s');

    // Serialize the data in order to store it in the export file.
    $serialized_data_arr = [];
    foreach($group_pages as $group_page) {
        $serialized_data_arr[] = $group_page->serialize();
    }
    $serialized_data = json_encode($serialized_data_arr);

    // Create the export as a 'ser' file.
    $export_file_name = plugin_dir_path( __FILE__ ).'/exports/export_' . $date_str . '.json';
    if ($configs["isDevelopment"]) {
        $export_file_name = plugin_dir_path( __FILE__ ).'\\exports\\export_' . $date_str . '.json';
    }
    $export_file = fopen($export_file_name, 'w');
    fwrite($export_file, $serialized_data);
    return fclose($export_file);
}

// Had to rename this to avoid 'duplicate function' errors
function write_group_pages_to_export_log($group_pages) {
    global $configs;

    // Get time to differentiate log file
    $date = new DateTime('now');
    $date->setTimezone(new DateTimeZone('America/New_York'));
    $date_str = $date->format('Y-m-d_H_i_s');

    // Create the text log
    $log_text = "";
    foreach ($group_pages as $index => $group_page) {
        $log_text .= "Group Page #" . ($index+1);
        $log_text .=  "\n" . $group_page->serialize_to_text();
    }

    // Write to the log file
    $log_file_name = plugin_dir_path( __FILE__ ).'/logs/export_log_' . $date_str . '.txt';
    if ($configs["isDevelopment"]) {
        $log_file_name = plugin_dir_path( __FILE__ ).'\\logs\\export_log_' . $date_str . '.txt';
    }
    $log_file = fopen($log_file_name, 'w');
    fwrite($log_file, $log_text);
    return fclose($log_file);
}
?>