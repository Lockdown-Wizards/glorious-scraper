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

if ( ! wp_next_scheduled( 'gr_cron_hook' ) ) {
    wp_schedule_event( time()+15, 'twicedaily', 'gr_cron_hook' ); // Execute 15 seconds from now
}
else {
    $timestamp = wp_next_scheduled( 'gr_cron_hook' );
    wp_unschedule_event( $timestamp, 'gr_cron_hook' );
    wp_schedule_event( time()+15, $_POST['cronjobRecurrence'], 'gr_cron_hook' ); // Execute 15 seconds from now
}

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>