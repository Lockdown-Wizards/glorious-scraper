<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

global $wpdb;
$result = $_POST['new'];
$table_name = $wpdb->prefix . "gr_fbgroups";
$wpdb->insert($table_name, array('url' => $result, 'active' => "1"));

// error_log(ABSPATH);
error_log($result);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper');
?>