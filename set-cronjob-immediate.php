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

error_log("In SCI: CJR = " . $_POST['cronjobRecurrence']);

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
    $opt_updated = update_option($opt_name, $_POST['cronjobRecurrence']);
    error_log("Option update: " . ($opt_updated ? "Success" : "Failure"));
}
else {
    // The option doesn't exist in the database. Use add function.
    $opt_added = add_option($opt_name, $_POST['cronjobRecurrence']);
    error_log("Option added: " . ($opt_added ? "Success" : "Failure"));
}

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>