<?php
// Access the plugin config
$configs = include('config.php');

// Get the name of the folder which wordpress resides in. (Only needed for development builds)
$folder_name = null;
if ($configs["isDevelopment"]) {
    $folder_name = explode('/', explode('/wp-content', str_replace('\\', '/', __DIR__))[0]);
    $folder_name = $folder_name[count($folder_name)-1];
}

// Access the wordpress database
if ($configs["isDevelopment"]) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/' . $folder_name . '/wp-load.php'); // Development
}
else {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
}

global $wpdb;
$id = $_POST['id'];
$url = $_POST['url'];

// update the url to the new value
// Grab all group URL's from database.
$table_name = $wpdb->prefix . "gr_fbgroups";
$wpdb->update($table_name, array('url' => $url), array('id' => $id));

error_log($id);
error_log($url);

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/' . $folder_name . '/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
