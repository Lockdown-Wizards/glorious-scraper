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

if ( ! wp_next_scheduled( 'gr_cron_hook' ) ) {
    wp_schedule_event( time()+15, $_POST['cronjobRecurrence'], 'gr_cron_hook' ); // Execute 15 seconds from now
}
else {
    $timestamp = wp_next_scheduled( 'gr_cron_hook' );
    wp_unschedule_event( $timestamp, 'gr_cron_hook' );
    wp_schedule_event( time()+15, $_POST['cronjobRecurrence'], 'gr_cron_hook' ); // Execute 15 seconds from now
}

$opt_name = 'gr_cron_option';
if (get_option($opt_name)) {    
    // The option exists in the database. Use update function.
    update_option($opt_name, $_POST['cronjobRecurrence']);
}
else {
    // The option doesn't exist in the database. Use add function.
    add_option($opt_name, $_POST['cronjobRecurrence']);
}

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/' . $folder_name . '/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>