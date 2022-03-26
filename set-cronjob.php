<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php'); // Development
//require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'); // Production
date_default_timezone_set('America/New_York');

$gr_cron_hours = $_POST['hours'];
$gr_cron_minutes = $_POST['minutes'];
$gr_cron_recurrence = $_POST['cronjobRecurrence'];
$gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );

// For safety, we will get rid of all current cronjobs with our hook 
while($gr_next_cronjob) {
    wp_unschedule_event( $gr_next_cronjob,  'gloriousrecovery_cronjob_hook' );
    $gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );
}

// so if $gr_cron_recurrent == 'none' we are done
if ($gr_cron_recurrence == 'daily' || 'twicedaily'){
    $gr_next_time = mktime((int)$gr_cron_hours, (int)$gr_cron_minutes, 0, (int)date("m"), (int)date("d"), date("Y"));
    wp_schedule_event( $gr_next_time, $gr_cron_recurrence, 'gloriousrecovery_cronjob_hook' );
}

header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
//header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
?>