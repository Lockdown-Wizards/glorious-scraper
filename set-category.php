<?php
/*
 * When supplied a category name and an event id, this script assigns the category to that event within 'the events calendar'.
*/

// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

if (!isset($_POST['category']) 
    || $_POST['category'] === ""
    || !isset($_POST['eventId'])
    || $_POST['eventId'] === "")
{
    // Cannot set the category for the event, missing required arguments (eventId and category)
    echo json_encode(false);
    exit();
}

$category_slug = strtolower($_POST['category']);

/*$term_taxonomy_table_name = $wpdb->prefix . "term_taxonomy";
$terms_table_name = $wpdb->prefix . "terms";
$sql = "SELECT term_taxonomy_id 
        FROM $term_taxonomy_table_name
        WHERE term_id = (SELECT term_id 
            FROM $terms_table_name
            WHERE slug = 'wellness');";
$category_taxonomy_id = $wpdb->get_results($sql)[0];*/

// Checks to see if the category exists within the database.
$terms_table_name = $wpdb->prefix . "terms";
$sql = "SELECT term_id 
        FROM $terms_table_name
        WHERE slug = '$category_slug';";
$term_ids = $wpdb->get_results($sql);

if (count($term_ids) > 0) {
    
    // Check to see if the category is already paired to the event.
    /*$term_relationship_table_name = $wpdb->prefix . "term_relationships";
    $sql = "SELECT * 
            FROM $term_relationship_table_name
            WHERE object_id = '".$_POST['eventId']."'
            AND term_taxonomy_id = '$category_taxonomy_id';";
    $results = $wpdb->get_results($sql);

    if (count($results) > 0) {
        // Category already exists, do nothing
        echo json_encode(false);
    }
    else {
        // Tie the category to the event.
        do_action('add_term_relationship', $_POST['eventId'], $category_taxonomy_id, 'tribe_events_cat');
        echo json_encode(true);
    }*/
    echo json_encode(!is_wp_error(wp_set_object_terms($_POST['eventId'], $term_ids[0], 'tribe_events_cat')));
}
else {
    // The supplied category does not exist in the database
    echo json_encode(false);
}
?>