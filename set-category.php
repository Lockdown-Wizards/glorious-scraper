<?php
/*
 * When supplied a category name and an event id, this script assigns the category to that event within 'the events calendar'.
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

if (!isset($_POST['category']) 
    || $_POST['category'] === ""
    || !isset($_POST['eventId'])
    || $_POST['eventId'] === "")
{
    // Cannot set the category for the event, missing required arguments (eventId and category)
    echo json_encode(false);
    exit();
}

// Trim off the excaped quotations
$category_slug = substr(strtolower($_POST['category']), 2, -2);
$event_id = intval(substr($_POST['eventId'], 2, -2));

// Checks to see if the category exists within the database.
$terms_table_name = $wpdb->prefix . "terms";
$sql = "SELECT term_id 
        FROM $terms_table_name
        WHERE slug = '$category_slug';";
$term_ids = $wpdb->get_results($sql);

if (count($term_ids) > 0) {
    echo json_encode(!is_wp_error(wp_set_post_terms($event_id, [$term_ids[0]->term_id], 'tribe_events_cat')));
}
else {
    // The supplied category does not exist in the database
    echo json_encode(false);
}
?>