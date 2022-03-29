<?php
/*
 * Takes all facebook group urls from the database and feeds them one by one to the scraper.php script.
 * Once the scraper.php returns the arguments array for each event, the arguments are fed into the set_event function (which calls the set-event.php script) to add or update the events.
 * Finally, this script will echo info about the events it has set.
*/

// Access the plugin config
$configs = include('config.php');

// Access the wordpress database
if ($configs["isDevelopment"]) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
}
else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
}

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// get the url from body of request
$url = $_POST['url'];

$request = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $url]);
// If the request was successful, then echo the request's body.
if ($request->status_code === 200) {
    // Add each event to 'the events calendar' plugin.
    $allArgs = json_decode($request->body);
    $actionsTaken = ""; // Shows what events have been saved as drafts in 'the events calendar' plugin.
    foreach ($allArgs as $args) {
        $postId = set_event($args->event);
        $actionsTaken .= "(".$args->event->Organizer.") Draft set for '" . $args->event->post_title . "' with event id: " . $args->event->id . "\n";
        $venueId = set_venue($args->venue);
        if ($venueId) {
            $actionsTaken .= "(".$args->venue->City.", ".$args->venue->State.") New venue '".$args->venue->Venue."' created with venue id: ".$venueId."\n";
        }
    }
    echo json_encode($actionsTaken);
}

// Creates or updates an event in 'the events calendar' depending on the id supplied in the args array.
// Reference to args for this function: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
function set_event($args)
{
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-event.php", [], ["args" => $args]);
}

// Creates or updates a venue in 'the events calendar.'
// Reference to args for this function: https://docs.theeventscalendar.com/reference/functions/tribe_create_venue/
function set_venue($args)
{
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/set-venue.php", [], ["args" => $args]);
}