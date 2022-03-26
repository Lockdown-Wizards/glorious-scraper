<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production

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

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>