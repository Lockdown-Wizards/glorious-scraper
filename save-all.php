<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production

global $wpdb;
$id = $_POST['id'];
$url = $_POST['url'];

// update the url to the new value
// Grab all group URL's from database.
$table_name = $wpdb->prefix . "gr_fbgroups";
$wpdb->update($table_name, array('url' => $url), array('id' => $id));

//error_log($id);
//error_log($url);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
