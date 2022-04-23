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

$opt_name = 'scraper_organization_name';
$highlighted_organization = get_option($opt_name);

$log_text = "";
$gr_log_text = "";

$log_text .= "*==================================================*\n";
$log_text .= "|                  Other Events  \n";
$log_text .= "*==================================================*\n\n";

$gr_log_text .= "*==================================================*\n";
$gr_log_text .= "|                Highlighted Events  \n";
$gr_log_text .= "|               " . $highlighted_organization . "  \n";
$gr_log_text .= "*==================================================*\n\n";

$grnl_events = tribe_get_events([
    'ends_after'     => 'now',
]);

foreach($grnl_events as $event) {
    // The Group Name will be in the event somewhere 
    // ------------- $haystack,             $needle
    if(str_contains($event->post_content, $highlighted_organization)) {
        // Format is Image, Title, Datetime, Venue, Description
        $gr_log_text  .= "<div class=\"mcnTextContent\">";
        $gr_log_text  .= "<img class=\"mcnImage\" width=\"500px\" src=\"" . tribe_event_featured_image($event, 'full', False, False) . "\">\n"; 
        $gr_log_text  .= "<p><strong><span style=\"font-size:24px\">" . $event->post_title . "</span></strong><br>\n";
        $gr_log_text  .= "<span style=\"font-size:18px\"> " . tribe_get_start_date($event, True, "l - M j - g:i A") . "</span> <br></p>\n";
        if(tribe_has_venue($event)) {
            $gr_log_text  .= "<p> " . tribe_get_address($event) . ", " . tribe_get_city($event) . " " . tribe_get_state($event) . " " . tribe_get_zip($event) . "</p><br><br>\n";
        }
        $gr_log_text  .= "<p> " . $event->post_content . "</p> <br></div>\n\n\n" ;
    }
    else {
        $log_text  .= "<div class=\"mcnTextContent\">";
        $log_text  .= "<img class=\"mcnImage\" width=\"500px\" src=\"" . tribe_event_featured_image($event, 'full', False, False) . "\">\n"; 
        $log_text  .= "<p><strong><span style=\"font-size:24px\">" . $event->post_title . "</span></strong><br>\n";
        $log_text  .= "<span style=\"font-size:18px\"> " . tribe_get_start_date($event, True, "l - M j - g:i A") . "</span> <br></p>\n";
        if(tribe_has_venue($event)) {
            $log_text  .= "<p> " . tribe_get_address($event) . ", " . tribe_get_city($event) . " " . tribe_get_state($event) . " " . tribe_get_zip($event) . "</p><br><br>\n";
        }
        $log_text  .= "<p> " . $event->post_content . "</p> <br></div>\n\n\n" ;
    }
}
// Get time to differentiate log file
$date = new DateTime('now');
$date->setTimezone(new DateTimeZone('America/New_York'));
$date_str = $date->format('Y-m-d');

// this is the format that works for me, need to check on production
$log_file_name = rtrim(plugin_dir_path( __FILE__ ), "/").'\\logs\\NewsletterData_' . $date_str . '.txt';
$log_file = fopen($log_file_name, 'w');
fwrite($log_file, $gr_log_text . "\n\n" . $log_text);
fclose($log_file);

if ($configs["isDevelopment"]) {
    header('Location: http://localhost/wordpress/wp-content/plugins/glorious-scraper/logs/NewsletterData_' . $date_str . '.txt'); // Development
}
else {
    header('Location: /wp-content/plugins/glorious-scraper/logs/NewsletterData_' . $date_str . '.txt'); // Production
}

?>