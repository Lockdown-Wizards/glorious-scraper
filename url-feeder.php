<?php
/*
 * Takes all facebook group urls from the database and feeds them one by one to the scraper.php script.
 * Once the scraper.php returns the arguments array for each event, the arguments are fed into the set_event function (which calls the set-event.php script) to add or update the events.
 * Finally, this script will echo info about the events it has set.
*/

// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// // Grab all group URL's from database.
// $table_name = $wpdb->prefix . "gr_fbgroups";
// $urls = $wpdb->get_results("SELECT * FROM $table_name");

// error_log(json_encode($urls));

// // If there are any urls that have been entered, then make a request.
// $request = "No URLs to scrape from $table_name!";

// Creates or updates an event in 'the events calendar' depending on the id supplied in the args array.
// Reference to args for this function: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
function set_event($args)
{
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-event.php", [], ["args" => $args]);
}

function set_venue($args)
{
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-venue.php", [], ["args" => $args]);
}

// get the url from body of request
$url = $_POST['url'];

$request = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $url]);
// If the request was successful, then echo the request's body.
if ($request->status_code === 200) {
    echo $request->body;
    // error_log(json_encode($request->body)); // Uncomment this line to see the request's body.

    // Add each event to 'the events calendar' plugin.
    $eventsArgs = json_decode($request->body);
    $actionsTaken = ""; // Shows what events have been saved as drafts in 'the events calendar' plugin.
    foreach ($eventsArgs as $args) {
        $postId = set_event($args);
        $actionsTaken .= "Draft set for '" . $args->post_title . "' with event id: " . $args->id . "\n";
    }
}