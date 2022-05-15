<?php
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

// Verify that the file uploaded is valid.
$target_dir = "temp/";
$target_file = plugin_dir_path( __FILE__ ) . "/" . $target_dir . basename($_FILES["importFile"]["name"]);
if ($configs["isDevelopment"]) {
    $target_dir = "temp\\";
    $target_file = plugin_dir_path( __FILE__ ) . "\\" . $target_dir . basename($_FILES["importFile"]["name"]);
}
$uploadSuccess = true;
$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Allow certain file formats
if($fileType != "json") {
  echo "Sorry, only json files are allowed.";
  $uploadSuccess = false;
}

// Check if $uploadSuccess is set to false by an error
if ($uploadSuccess == false) {
  echo "This file was not able to be imported. Please try another file.";
  exit();
} 

// Unserialize the json data so that we can utilize GroupPage and EventPage objects.
$serialized_group_pages = json_decode(file_get_contents($_FILES["importFile"]["tmp_name"]));
$group_pages = [];
foreach($serialized_group_pages as $group_page_data) {
    $obj = new GroupPage("");
    $obj->unserialize($group_page_data);
    $group_pages[] = $obj;
}

// Store the events and venues of each event page.
foreach ($group_pages as $group_page) {
    if (!$group_page->get_has_scraped()) {
        // Skip group pages that failed to be scraped
        continue;
    }
    for ($i = 0, $total_pages = $group_page->get_total_event_pages(); $i < $total_pages; $i++) {
        $event_data = $group_page->get_event_page($i)->get_event();

        if ($event_data["post_title"] === "") {
            $group_page->get_event_page($i)->set_event_status("Event missing title.");
            $group_page->get_event_page($i)->set_venue_status("Event creation failed, no venue was stored.");
            continue;
        }
        // Set the event in 'the events calendar'
        $response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-event.php", [], ['args' => json_encode($event_data)]);
        if ($response->body !== "false") {
            $group_page->get_event_page($i)->set_has_created_event(true);
        }
        else {
            //$group_page->get_event_page($i)->set_has_created_event(false);
            $group_page->get_event_page($i)->set_event_status("Request for event creation failed.");
            $group_page->get_event_page($i)->set_venue_status("Event creation failed, no venue was stored.");
            continue;
        }

        // Set the venue in 'the events calendar'
        $location = $event_data["Location"];
        if ($location !== "" && !str_contains($location, "http")) {
            $response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-venue.php", [], ['args' => json_encode($group_page->get_event_page($i)->get_venue())]);
            if ($response->body !== "false") {
                $group_page->get_event_page($i)->set_has_created_venue(true);
            }
            else {
                //$group_page->get_event_page($i)->set_has_created_venue(false);
                $group_page->get_event_page($i)->set_venue_status("Request for venue creation failed.");
            }
        }
        else {
            //$group_page->get_event_page($i)->set_has_created_venue(false);
            $group_page->get_event_page($i)->set_venue_status("No location was scraped.");
        }
    }
}


if ($configs["enableCronjobLogger"]) {
    // Write all data obtained during the scrape into a seperate log file within the log folder.
    write_group_pages_to_import_log($group_pages);
    echo json_encode("Import completed.");
}
else {
    // Prepare the results of the scrape for sending back to the front end (for testing purposes)
    $results = [];
    foreach ($group_pages as $group_page) {
        $results[] = $group_page->to_array();
    }
    echo json_encode($results);
}

// Had to rename this to avoid 'duplicate function' errors
function write_group_pages_to_import_log($group_pages) {
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
    $log_file_name = plugin_dir_path( __FILE__ ).'/logs/import_log_' . $date_str . '.txt';
    if ($configs["isDevelopment"]) {
        $log_file_name = plugin_dir_path( __FILE__ ).'\\logs\\import_log_' . $date_str . '.txt';
    }
    $log_file = fopen($log_file_name, 'w');
    fwrite($log_file, $log_text);
    return fclose($log_file);
}

?>