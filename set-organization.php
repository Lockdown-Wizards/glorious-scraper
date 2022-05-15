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

$opt_name = 'scraper_organization_name';
$organization = $_POST['organization'];
$organization_opt = get_option($opt_name);
if ($organization_opt) {    
    // The option exists in the database. Use update function.
    update_option($opt_name, $organization);
}
else {
    // The option doesn't exist in the database. Use add function.
    add_option($opt_name, $organization);
}

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/' . $folder_name . '/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>