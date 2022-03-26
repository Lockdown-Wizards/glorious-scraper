<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
global $wpdb;

$result = $_POST['new'];
$table_name = $wpdb->prefix . "gr_fbgroups";
$wpdb->insert($table_name, array('url' => $result, 'active' => "1"));

//error_log($result);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>