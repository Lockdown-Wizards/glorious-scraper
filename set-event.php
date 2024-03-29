<?php
/*
 * Utilizes 'the events calendar' API to create or update an event that will appear under the events tab.
 * Specify 'id' => 0 in the args array to create a new event.
 * Otherwise, give a specific id and this will update the already existing event.
*/

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

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Load the events calendar event creation API
require_once dirname(__DIR__) . '/the-events-calendar/src/functions/php-min-version.php'; // Load the required php min version functions.
require_once dirname(__DIR__) . '/the-events-calendar/vendor/autoload.php'; // Load the Composer autoload file.
require_once dirname(__DIR__) . '/the-events-calendar/src/Tribe/Main.php'; // Loads 'the events calendar' main
require_once dirname(__DIR__) . '/the-events-calendar/src/Tribe/API.php'; // Loads 'the events calendar' api main
Tribe__Events__Main::instance(); // Create an instance of 'the events calendar' main singleton
require_once dirname(__DIR__) . '/the-events-calendar/src/functions/advanced-functions/event.php'; // Load the script needed to create events.

if (isset($_POST['args'])) {
    // Documentation for all args: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
    $args = (array) json_decode(stripslashes($_POST['args']));
    $postId = intval($args['id']);

    // If the id given is 0, search the database for the most recent post, then add one to it.
    if ($postId === 0) {
        $sql = "SELECT ID FROM `wp_posts` ORDER BY `ID` DESC LIMIT 1;";
	    $result = $wpdb->get_results($sql);
        $postId = intval($result[0]->ID) + 1;
    }

    // Since it's possible to enter in the wrong ID key in the args array, this code ensures that the ID key will be 'ID' if the post already exists or 'import_id' if the post doesn't yet exist.
    $postIdArg = (get_post_status($postId)) ? 'ID' : 'import_id'; // https://stackoverflow.com/questions/41655064/why-wp-update-post-return-invalid-post-id
    unset($args['id']);
    $updatedArgs = [$postIdArg => $postId];
    foreach ($args as $key => $arg) {
        if ($arg !== null) {
            $updatedArgs[$key] = $arg;
        }
    }
    
    // If you're not getting any results, then edit line 102 in '\the-events-calendar\src\functions\advanced-functions\event.php' to 'return $postId;' to see error messages.
    echo json_encode(tribe_create_event($updatedArgs));
}
else {
    echo 'Args not supplied to the set-event script. Cannot add event to the database. Did you forget to add [\'args\' => $args] as your post request body?';
}

?>