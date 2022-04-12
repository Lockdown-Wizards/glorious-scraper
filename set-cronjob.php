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

// clear last cronjob
if(wp_next_scheduled( 'gr_cron_hook' )) {
    $timestamp = wp_next_scheduled( 'gr_cron_hook' );
    wp_unschedule_event( $timestamp, 'gr_cron_hook' );
}

// hours == -1 when we want 15s from now
if($_POST['hours'] == -1) {
    wp_schedule_event( time()+15, $_POST['cronjobRecurrence'], 'gr_cron_hook' ); 
}
// otherwise we want a specific time
elseif ($_POST['cronjobRecurrence'] == 'daily' || 'twicedaily') {
    $gr_timezone_GMT = new DateTimeZone("Europe/London");
    $gr_datetime_GMT = new DateTime("now", $gr_timezone_GMT);
    
    $gr_datetime_offset = 0;
    $gr_timezone = get_option('scraper_timezone');
    $gr_timezone_here = new DateTimeZone($gr_timezone ? $gr_timezone : 'America/New_York'); // default to Eastern Time

    $gr_datetime_offset = floor(timezone_offset_get($gr_timezone_here, $gr_datetime_GMT)/3600);
    $gr_cron_hours = $_POST['hours'] - $gr_datetime_offset;
    $gr_past_midnight_offset = ($gr_cron_hours >= 24) ? -1 : 0; 
    
    // We actually don't need to account for month and year boundaries as mktime() accounts for this!
    $gr_next_time = mktime((int)$gr_cron_hours, (int) $_POST['minutes'], 0, (int)date("m"), (int)date("d") + $gr_past_midnight_offset, date("Y"));
    wp_schedule_event( $gr_next_time, $_POST['cronjobRecurrence'], 'gr_cron_hook' );
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
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
?>