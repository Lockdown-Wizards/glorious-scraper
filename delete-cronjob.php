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

$gr_next_cronjob = wp_next_scheduled( 'gr_cron_hook' );

while($gr_next_cronjob) {
    wp_unschedule_event( $gr_next_cronjob,  'gr_cron_hook' );
    $gr_next_cronjob = wp_next_scheduled( 'gr_cron_hook' );
}


if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>