<?php
/*
 * Handles pairing a venue to an event.
 * (input) venueId: The ID of the venue in the database.
 * (input) eventId: The ID of the event in the database.
 * (output) int/bool: The new meta field ID if a field with the given key didn't exist and was therefore added, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
*/

// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

if (isset($_POST['venueId']) && isset($_POST['eventId'])) {
    echo json_encode(update_metadata('post', json_decode($_POST['eventId']), '_EventVenueID', json_decode($_POST['venueId'])));
}
else {
    echo 'Either venueId or eventId was not supplied. Syntax for posting should look like this: [\'venueId\' => $venueId, \'eventId\' => $eventId]';
}