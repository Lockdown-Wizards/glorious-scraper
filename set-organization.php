<?php
// Access the plugin config
$configs = include('config.php');

// Access the wordpress database
if ($configs["isDevelopment"]) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
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
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>