<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production

$gr_cron_hours = $_POST['hours'];
$gr_cron_minutes = $_POST['minutes'];
$gr_cron_recurrence = $_POST['cronjobRecurrence'];
$gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );
$gr_timezone = get_option('scraper_timezone');

//date_default_timezone_set($gr_timezone); 

// For safety, we will get rid of all current cronjobs with our hook 
while($gr_next_cronjob) {
    wp_unschedule_event( $gr_next_cronjob,  'gloriousrecovery_cronjob_hook' );
    $gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );
}

if ($gr_cron_recurrence == 'daily' || 'twicedaily'){
    
    $gr_timezone_GMT = new DateTimeZone("Europe/London");
    $gr_datetime_GMT = new DateTime("now", $gr_timezone_GMT);
    
    $gr_datetime_offset = 0;
    if ($gr_timezone) {
        $gr_timezone_here = new DateTimeZone($gr_timezone);
    }
    else {
        // Default to Eastern
        $gr_timezone_here = new DateTimeZone('America/New_York');
    }

    $gr_datetime_offset = floor(timezone_offset_get($gr_timezone_here, $gr_datetime_GMT)/3600);
    $gr_cron_hours -= $gr_datetime_offset;
    $gr_past_midnight_offset = ($gr_cron_hours >= 24) ? -1 : 0;
    //error_log("Past midnight offset: " . $gr_past_midnight_offset); 
    
    // We actually don't need to account for month and year boundaries as mktime() accounts for this!
    $gr_next_time = mktime((int)$gr_cron_hours, (int)$gr_cron_minutes, 0, (int)date("m"), (int)date("d") + $gr_past_midnight_offset, date("Y"));
    wp_schedule_event( $gr_next_time, $gr_cron_recurrence, 'gloriousrecovery_cronjob_hook' );
}

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>