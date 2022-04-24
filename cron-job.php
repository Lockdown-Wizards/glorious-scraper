<?php
/*
 * This is the script which should be run every time the cron-job executes.
*/

// This must be in a function so that we can assign it to a hook for the cron job to work.
function glorious_cronjob() {
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

    // Store the events and venues of each event page.
    foreach ($group_pages as $group_page) {
        if (!$group_page->get_has_scraped()) {
            // Skip group pages that failed to be scraped
            continue;
        }
        for ($i = 0, $total_pages = $group_page->get_total_event_pages(); $i < $total_pages; $i++) {
            if ($group_page->get_event_page($i)->get_event()->post_title === "") {
                $group_page->get_event_page($i)->set_event_status("Event missing title.");
                $group_page->get_event_page($i)->set_venue_status("Event creation failed, no venue was stored.");
                continue;
            }
            // Set the event in 'the events calendar'
            $response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-event.php", [], ['args' => json_encode($group_page->get_event_page($i)->get_event())]);
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
            $location = $group_page->get_event_page($i)->get_event()->Location;
            if ($location !== "" && !str_contains($location, "http")) {
                $venue_response = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-venue.php", [], ['args' => json_encode($group_page->get_event_page($i)->get_venue())]);
                if ($venue_response->body !== "false") {
                    $group_page->get_event_page($i)->set_has_created_venue(true);
                    $venue_link_status = update_metadata('post', $response, '_EventVenueID', $venue_response); //functions as pair-venue-to-event.php
                    if($venue_link_status) {
                        $group_page->get_event_page($i)->set_venue_status("Successfully linked venue to event. Event response [" . $response . "], Venue response [" . $venue_response . "]" );
                    }
                    else {
                        $group_page->get_event_page($i)->set_venue_status("Did not link venue to event. Event response [" . $response . "], Venue response [" . $venue_response . "]");
                    }
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
        write_group_pages_to_log($group_pages);
        echo json_encode("Cron-job completed.");
    }
    else {
        // Prepare the results of the scrape for sending back to the front end (for testing purposes)
        $results = [];
        foreach ($group_pages as $group_page) {
            $results[] = $group_page->serialize();
        }
        echo json_encode($results);
    }
}

function write_group_pages_to_log($group_pages) {
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
    $log_file_name = plugin_dir_path( __FILE__ ).'\\logs\\scrape_log_' . $date_str . '.txt';
    $log_file = fopen($log_file_name, 'w');
    fwrite($log_file, $log_text);

    $opt_name = 'gr_last_scrape_log';
    $scrape_log_opt = get_option($opt_name);
    if ($scrape_log_opt) {    
        // The option exists in the database. Use update function.
        update_option($opt_name, "scrape_log_" . $date_str . ".txt");
    }
    else {
        // The option doesn't exist in the database. Use add function.
        add_option($opt_name, "scrape_log_" . $date_str . ".txt");
    }

    return fclose($log_file);
}
?>