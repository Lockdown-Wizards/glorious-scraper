<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
date_default_timezone_set('America/New_York');

$gr_cron_hours = $_POST['hours'];
$gr_cron_minutes = $_POST['minutes'];
$gr_cron_recurrence = $_POST['cronjobRecurrence'];
$gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );

// We want no cronjob
if ($gr_cron_recurrence == 'none'){
    if(!$gr_next_cronjob) {
        // do nothing, we want no cronjob and we have no cronjob
    }
    else {
        // get rid of current cronjob
        wp_unschedule_event( $gr_next_cronjob,  'gloriousrecovery_cronjob_hook' );
    }
}
else {
    if(!$gr_next_cronjob) {
        // we want a cronjob and there is one
        wp_unschedule_event( $gr_next_cronjob,  'gloriousrecovery_cronjob_hook' );
    }
    // $gr_cron_recurrence is either 'daily' or 'twicedaily'
    $gr_next_time = mktime((int)$gr_cron_hours, (int)$gr_cron_minutes, 0, (int)date("m"), (int)date("d"), date("Y"));
    wp_schedule_event( $gr_next_time, $gr_cron_recurrence, 'gloriousrecovery_cronjob_hook' );
}

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper');
?>