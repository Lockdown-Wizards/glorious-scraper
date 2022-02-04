<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');

global $wpdb;
$wpdb->delete("wp_gr_scraper_urls", array('id' => $_POST['delete']));
error_log($_POST['delete']);

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper');
?>