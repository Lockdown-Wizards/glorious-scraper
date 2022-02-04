<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

global $wpdb;
$result = $_POST['new'];
$wpdb->insert("wp_gr_scraper_urls", array('url' => $result, 'active' => "1"));

// error_log(ABSPATH);
error_log($result);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper');
?>