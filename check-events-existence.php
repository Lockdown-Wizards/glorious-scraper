<?php
/*
 * When given an array of event ids, check each id to see if it exists in the database. 
 * If an event id exists, then pair that id to the content that it normally displays.
 * Otherwise, pair that id to a boolean false.
 * 
 * NOTE: When sending the array through FormData, don't forget to use JSON.stringify on the array.
*/

// Check that this script received an array with at least one item before continuing.
if ($_POST["ids"] === NULL) {
    echo "'ids' array was not sent. Proper usage involves performing a POST request which includes an 'ids' array full of event id strings in the form data.";
    exit(); 
}
$ids = json_decode($_POST["ids"]);
if (gettype($ids) !== "array") { 
    echo "'ids' was not sent as an array. Proper usage involves performing a POST request which includes an 'ids' array full of event id strings in the form data.";
    exit(); 
}
else if (count($ids) === 0) {
    echo "No event id strings were supplied in the 'ids' array. Proper usage involves performing a POST request which includes an 'ids' array full of event id strings in the form data.";
    exit();
} 

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
$table_name = $wpdb->prefix . "posts";

// Form the sql query that will check each id and then return all existing events.
$sql = "SELECT id, post_content FROM $table_name WHERE ";
$totalIds = count($ids);
foreach($ids as $index => $id) {
    if ($index+1 === $totalIds) {
        $sql .= "id = '$id'";
    }
    else {
        $sql .= "id = '$id' or ";
    }
}
$results = $wpdb->get_results($sql);

// Find the event ids that don't exist in the database and mark them as false.
// Also, format the results such that the event id is the key and the content is the value.
$fullResults = [];
foreach ($ids as $possibleId) {
    $exists = false;
    foreach ($results as $result) {
        if (intval($result->id) === intval($possibleId)) {
            $fullResults[$result->id] = $result->post_content;
            $exists = true;
        }
    }
    if (!$exists) {
        $fullResults[$possibleId] = false;
    }
}

// Send response back.
echo json_encode($fullResults);
?>