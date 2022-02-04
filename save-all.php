<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

global $wpdb;
$id = $_POST['id'];
$url = $_POST['url'];

// update the url to the new value
$wpdb->update("wp_gr_scraper_urls", array('url' => $url), array('id' => $id));

error_log($id);
error_log($url);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper');
