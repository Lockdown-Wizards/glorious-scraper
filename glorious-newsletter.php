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

require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.

$log_text = "";
$grnl_events = tribe_get_events([
    'ends_after'     => 'now',
]);

//error_log("Events in newsletter: " . print_r($grnl_events, True));

foreach($grnl_events as $event) {
    // Format is Image, Title, Datetime, Venue, Description
    $log_text .= "<div class=\"mcnTextContent\">";
    $log_text .= "<img class=\"mcnImage\" width=\"600px\" src=\"" . tribe_event_featured_image($event, 'full', False, False) . "\">\n"; 
    $log_text .= "<h3>" . $event->post_title . "</h3>\n";
    $log_text .= " " . tribe_get_start_date($event, True, "D - M j - g:i A") . " <br>\n";
    if(tribe_has_venue($event)) {
        $log_text .= " " . tribe_get_address($event) . ", " . tribe_get_city($event) . " " . tribe_get_state($event) . " " . tribe_get_zip($event) . "<br><br>\n";
    }
    $log_text .= " " . $event->post_content . " <br></div>\n\n\n" ;
}
// Get time to differentiate log file
$date = new DateTime('now');
$date->setTimezone(new DateTimeZone('America/New_York'));
$date_str = $date->format('Y-m-d');

$log_file_name = rtrim(plugin_dir_path( __FILE__ ), "/").'\\NewsletterData_' . $date_str . '.txt';
//error_log("log file name: " . $log_file_name);
$log_file = fopen($log_file_name, 'w');
fwrite($log_file, $log_text);
fclose($log_file);

/*
if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-admin/admin.php?page=event-scraper'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}
*/
if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-content/plugins/glorious-scraper/NewsletterData_' . $date_str . '.txt'); // Development
}
else {
    header('Location: /wp-admin/admin.php?page=event-scraper'); // Production
}

?>