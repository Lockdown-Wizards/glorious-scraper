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
    
    $title = extract_fb_event_title($event_dom);
    $datetime = extract_fb_event_datetime($event_dom);
    $organization = extract_fb_event_organization($event_dom);

    $event->set_title($title);
    $event->set_description(extract_fb_event_description($event_dom));
    $event->set_slug(urlencode($title));
    $event->set_start_date(start_date_from_datetime($datetime)); // Might need to give the events class this string so that it can extract what it needs from it. Needs: start date, start time, end date, and end time.
    $event->set_start_time(start_time_from_datetime($datetime));
    $event->set_location(extract_fb_event_location($event_dom));
    $event->set_image(extract_fb_event_image($event_dom));
    $event->set_organization($organization);
    $event->set_featured(get_option('scraper_organization_name') === $organization);
}

var_dump($events);
//$event_page = $fbSession->request($url);
//$postId = (get_post_status(12345678)) ? 'ID' : 'import_id';
// Reference for the args to put into this array
// https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
/*$eventCreationArgs = [
    "id" => 0, 
    "post_title" => "THIS IS AN OBVIOUS TEST SO JUST LOOK FOR THIS TEXT IN THE DB PLEASE", 
    "EventStartDate" => "2022-08-28 07:00:00", 
    "EventEndDate" => "2022-08-28 09:00:00"
];
$eventCreationRequest = create_event($eventCreationArgs);
echo json_encode($eventCreationRequest->body);*/
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

/*function extract_fb_event_start_date($dom) {
    $finder = new DomXPath($dom);
    $classname = "cs ct v cu cv";
    $nodes = $finder->query("//*[contains(@class, '$classname')]");
    return $nodes->item(0)->textContent;
}*/

// Given an events page, find and extract the date & time of the event.
function extract_fb_event_datetime($dom) {
    $finder = new DomXPath($dom);
    $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/yL/r/HvJ9U6sdYns.png';
    $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
    return $nodes->item(0)->parentNode->parentNode->textContent;
}

// Given an events page, find and extract the event image.
function extract_fb_event_image($dom) {
    $eventHeaderElem = $dom->getElementById('event_header');
    $images = $eventHeaderElem->getElementsByTagName('img');
    return $images->item(0)->getAttribute("src");
}

// Given an events page, find and extract the event address.
function extract_fb_event_location($dom) {
    $finder = new DomXPath($dom);
    $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/y_/r/_gA751gYiTQ.png';
    $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
    $addressElem = $nodes->item(0)->parentNode->parentNode->getElementsByTagName('dd');
    return $addressElem->item(0)->textContent;
}

// Given an events page, find and extract the organization running the event.
function extract_fb_event_organization($dom) {
    // //a[contains(@href, '/gloriousrecovery/?ref=page_internal')]
    $finder = new DomXPath($dom);
    //$href = 'https://static.xx.fbcdn.net/rsrc.php/v3/yL/r/HvJ9U6sdYns.png';
    $nodes = $finder->query("//div[contains(text(), '·')]"); // The element which contains the organization name is the only one with '·' in its text.
    return $nodes->item(0)->getElementsByTagName('a')->item(0)->textContent; // Find the <a> tag in this container, then extract its text.
}

// Given an events page, find and extract the event description.
function extract_fb_event_description($dom) {
    $eventTabsElem = $dom->getElementById('event_tabs');
    $descriptionContainerElem = $eventTabsElem->childNodes->item(1)->firstChild;
    return $descriptionContainerElem->childNodes->item(1)->textContent;
}

// Extracts the start time from a date time string obtained from the 'extract_fb_event_datetime' function.
function start_date_from_datetime($datetime) {
    return explode(" at ", $datetime)[0];
}

// Extracts the start time from a date time string obtained from the 'extract_fb_event_datetime' function.
function start_time_from_datetime($datetime) {
    $times = explode(" at ", $datetime)[1];
    return explode(" – ", $times)[0];
}

// Creates an event in 'the events calendar'.
// Reference to args for this function: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
function create_event($args) {
    return WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/create-event.php", [], ["args" => $args]);
}
?>