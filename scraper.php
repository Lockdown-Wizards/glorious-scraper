<?php
/*
 * The main job of this file is to scrape a given facebook group URL for event links found on the page.
 * Returns an array of Event objects.
 * These event objects will be added to the database in another file.
 *
*/

// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
global $wpdb;

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '\\glorious-scraper\\requests\\src\\Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Load the Event class
require_once(__DIR__ . '/Event.php');

use WpOrg\Requests\Session;

const MBASIC_URL = "https://mbasic.facebook.com";
const LOGIN_URL = "https://www.facebook.com/login/";

// Now let's make a request!
//$request = WpOrg\Requests\Requests::get('http://httpbin.org/get', ['Accept' => 'application/json']);
$url = $_POST['url'];
//$request = WpOrg\Requests\Requests::get($url, ['Accept' => 'application/json']);
$fbSession = new Session();
$group_page = $fbSession->request($url);
//$decodedBody = $page->decode_body();
//var_dump($decodedBody);
$dom = new DOMDocument(); // Create a new DOMDocument object which will be used for parsing through the html
@ $dom->loadHTML($group_page->body); // @ surpresses any warnings

$eventLinks = extract_fb_event_links($dom);

// Create an Event object for each link.
$events = [];
foreach($eventLinks as $eventLink) {
    $events[] = new Event($eventLink);
}

// For every link, scrape it for relevant event info.

foreach($events as $event) {
    $event_page = $fbSession->request(MBASIC_URL . $event->get_url());
    $event_dom = new DOMDocument();
    @ $event_dom->loadHTML($event_page->body); // @ surpresses any warnings
    
    $event->set_title(extract_fb_event_title($event_dom));
    $event->set_start_date(extract_fb_event_start_date($event_dom));
}
//$event_page = $fbSession->request($url);
//$postId = (get_post_status(12345678)) ? 'ID' : 'import_id';
// Reference for the args to put into this array
// https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
$eventCreationArgs = [
    "ID" => 12345678, 
    "post_title" => "THIS IS AN OBVIOUS TEST SO JUST LOOK FOR THIS TEXT IN THE DB PLEASE", 
    "EventStartDate" => "2022-08-28 07:00:00", 
    "EventEndDate" => "2022-08-28 09:00:00"
];
$eventCreationRequest = create_event($eventCreationArgs);
echo json_encode($eventCreationRequest->body);
// Check what we received
//var_dump($request);
//echo json_encode($page);
//echo $page->body;
//var_dump($events);

// Given a facebook group page, find and extract facebook event links.
// Returns an array of links.
function extract_fb_event_links($dom) {
    // Grab all <a> tags from the group page.
    $linkElems = $dom->getElementsByTagName("a");
    $hrefs = [];
    foreach($linkElems as $link) {
        $hasHref = $link->getAttribute("href") !== "";
        $isEventLink = str_contains($link->getAttribute("href"), '/events/');
        if ($hasHref && $isEventLink) {
            // We split the string by '?' to remove the lengthy query in the original url.
            $hrefs[] = explode("?", $link->getAttribute("href"))[0];
            //$hrefs[] = $BASE_URL . $link->getAttribute("href");
        }
    }
    return $hrefs;
}

// Given an events page, find and extract the event title.
function extract_fb_event_title($dom) {
    $loginBarElem = $dom->getElementById('mobile_login_bar');
    return explode(' is on Facebook', $loginBarElem->textContent)[0];
}

function extract_fb_event_start_date($dom) {
    $finder = new DomXPath($dom);
    $classname = "cs ct v cu cv";
    $nodes = $finder->query("//*[contains(@class, '$classname')]");
    return $nodes->item(0)->textContent;
}

// Creates an event in 'the events calendar'.
// Reference to args for this function: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
function create_event($args) {
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/create-event.php", [], ["args" => $args]);
}
?>