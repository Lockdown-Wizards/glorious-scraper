<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production

$opt_name_tz = 'scraper_timezone';
$gr_timezone = $_POST['timezone'];
$timezone_opt = get_option($opt_name_tz);
if ($timezone_opt) {    
    // The option exists in the database. Use update function.
    update_option($opt_name_tz, $gr_timezone);
}
else {
    // The option doesn't exist in the database. Use add function.
    add_option($opt_name_tz, $gr_timezone);
}

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>