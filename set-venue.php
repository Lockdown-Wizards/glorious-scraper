<?php
/*
 * Utilizes 'the events calendar' API to create or update a venue that will appear under the events tab.
 * This script will auto-detect if a venue already exists or not, so supplying an ID is not necessary (unlike set-event.php)
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

// Load the events calendar event creation API
require_once dirname(__DIR__) . '/the-events-calendar/src/functions/php-min-version.php'; // Load the required php min version functions.
require_once dirname(__DIR__) . '/the-events-calendar/vendor/autoload.php'; // Load the Composer autoload file.
require_once dirname(__DIR__) . '/the-events-calendar/src/Tribe/Main.php'; // Loads 'the events calendar' main
require_once dirname(__DIR__) . '/the-events-calendar/src/Tribe/API.php'; // Loads 'the events calendar' api main
Tribe__Events__Main::instance(); // Create an instance of 'the events calendar' main singleton
require_once dirname(__DIR__) . '/the-events-calendar/src/functions/advanced-functions/venue.php'; // load the script needed to create venues.

if (isset($_POST['args'])) {
    // Documentation for all args: https://docs.theeventscalendar.com/reference/functions/tribe_create_venue/
    $args = (array) json_decode(stripslashes($_POST['args']));

    /* 
     * All Venue metadata tags from the postmeta table in the database:
     * _VenueCountry
	 * _VenueAddress
	 * _VenueCity
	 * _VenueStateProvince
	 * _VenueState
	 * _VenueProvince
	 * _VenueZip
	 * _VenuePhone
	 * _VenueURL
	 * _VenueShowMap
	 * _VenueShowMapLink
    */

    /*
     * All args array values:
     * id
     * Venue
     * Country
     * City
     * State
     * Address
     * Province
     * Zip
     * Phone
    */ 

    // Every venue needs a title. Check if we have a title for this venue.
    if (!isset($args['Venue']) || $args['Venue'] === "") { 
        echo json_encode(false); // Missing venue title. This could be because the event is online.
        exit();
    }

    // Find a venue that matches the venue name from the args array.
    $posts_table_name = $wpdb->prefix . "posts";
    $postmeta_table_name = $wpdb->prefix . "postmeta";
    //$post_title_with_amps = str_replace("&", "&amp;", $args['Venue']);
    $post_title_htmlspecialchars = htmlspecialchars($args['Venue']); 
    $sql = "SELECT $posts_table_name.ID, $posts_table_name.post_title, $postmeta_table_name.meta_key, $postmeta_table_name.meta_value
            FROM $posts_table_name
            INNER JOIN $postmeta_table_name ON $postmeta_table_name.post_id = $posts_table_name.ID 
            WHERE $postmeta_table_name.meta_key LIKE '_Venue%' 
            AND $posts_table_name.post_title = '" . $post_title_htmlspecialchars . "';";
    $results = $wpdb->get_results($sql); // Contains results for a Venue with a title that matches the one given in the args array ($args['Venue']).
    
    // Detect whether or not the venue found in the database matches with the one outlined in the args array.
    $hasMatchingAddress = false;
    $hasMatchingCity = false;
    $hasMatchingState = false;
    foreach ($results as $metadata) {
        if ($metadata->meta_key === '_VenueAddress') {
            $hasMatchingAddress = $metadata->meta_value === $args['Address'];
        }
        else if ($metadata->meta_key === '_VenueCity') {
            $hasMatchingCity = $metadata->meta_value === $args['City'];
        }
        else if ($metadata->meta_key === '_VenueState') {
            $hasMatchingState = $metadata->meta_value === $args['State'];
        }
    }
    $hasMatchingVenue = $hasMatchingAddress && $hasMatchingCity && $hasMatchingState;

    // Set the post id for this venue if updating an existing venue.
    $postId = -1;
    if (count($results) > 0 && $hasMatchingVenue) {
        $postId = $results[0]->ID;
    }

    // Set the venue
    if ($postId === -1) {
        echo json_encode(tribe_create_venue($args));
    }
    else {
        echo json_encode(tribe_update_venue($postId, $args));
    }
}
else {
    echo 'Args not supplied to the set-venue script. Cannot add event to the database. Did you forget to add [\'args\' => $args] as your post request body?';
}