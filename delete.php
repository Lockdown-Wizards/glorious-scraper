<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production

global $wpdb;
// Grab all group URL's from database.
$table_name = $wpdb->prefix . "gr_fbgroups";
$wpdb->delete($table_name, array('id' => $_POST['delete']));
error_log($_POST['delete']);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>