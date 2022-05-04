<?php
/*
 * Handles pairing a venue to an event.
 * (input) venueId: The ID of the venue in the database.
 * (input) eventId: The ID of the event in the database.
 * (output) int/bool: The new meta field ID if a field with the given key didn't exist and was therefore added, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
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

if (isset($_POST['venueId']) && isset($_POST['eventId'])) {
    echo json_encode(update_metadata('post', json_decode($_POST['eventId']), '_EventVenueID', json_decode($_POST['venueId'])));
}
else {
    echo 'Either venueId or eventId was not supplied. Syntax for posting should look like this: [\'venueId\' => $venueId, \'eventId\' => $eventId]';
}