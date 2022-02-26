<?php
/*
 * The main job of this file is to scrape a given facebook group URL for event links found on the page.
 * Returns an array of Event arguments.
 * These event arguments will be fed into the set-events.php script to add and update events.
 *
*/

// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
global $wpdb;

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '/glorious-scraper/requests/src/Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Load the Event and Venue classes
require_once(__DIR__ . '/Event.php');
require_once(__DIR__ . '/Venue.php');

use WpOrg\Requests\Session;

const MBASIC_URL = "https://mbasic.facebook.com";
const NORMAL_URL = "https://www.facebook.com";

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

// Create a Venue object for each link.
$venues = [];
foreach($eventLinks as $eventLink) {
    $venues[] = new Venue();
}

// For every link, scrape it for relevant event info.
foreach($events as $i => $event) {
    $event_page = $fbSession->request(MBASIC_URL . $event->get_url());
    $event_dom = new DOMDocument();
    @ $event_dom->loadHTML($event_page->body); // @ surpresses any warnings

    $title = extract_fb_event_title($event_dom);
    $description = extract_fb_event_description($event_dom);
    $image = extract_fb_event_image($event_dom);
    $location = extract_fb_event_location($event_dom);
    $datetime = extract_fb_event_datetime($event_dom);
    $organization = extract_fb_event_organization($event_dom);
    $organization_url = extract_fb_event_organization_url($event_dom);

    $event->set_title($title);
    $event->set_description($description);
    $event->set_slug(urlencode($title));
    $event->set_location($location);
    $event->set_image($image);
    $event->set_start_date(start_date_from_datetime($datetime));
    $event->set_start_time(start_time_from_datetime($datetime));
    $event->set_end_date(end_date_from_datetime($datetime));
    $event->set_end_time(end_time_from_datetime($datetime));
    $event->set_organization($organization);
    $event->set_organization_url($organization_url);
    $event->set_featured(get_option('scraper_organization_name') === $organization);

    if ($location !== "") {
        $location_title = extract_fb_location_title($event_dom);
        $street = $city = $state = $zip = "";
        if (!location_is_online($location)) {
            $street = street_from_location($location);
            $city = city_from_location($location);
            $state = state_from_location($location);
            $zip = zip_from_location($location);
        }

        $venues[$i]->set_title($location_title);
        $venues[$i]->set_address($street);
        $venues[$i]->set_city($city);
        $venues[$i]->set_state($state);
        $venues[$i]->set_zip($zip);
    }
}

foreach($events as $event) {
    $event_page = $fbSession->request(NORMAL_URL . $event->get_url());
    $event_dom = new DOMDocument();
    @ $event_dom->loadHTML($event_page->body); // @ surpresses any warnings

    $ticket_url = extract_fb_event_ticket_url($event_dom);
    $categories = extract_fb_event_categories($event_dom);

    $event->set_ticket_url($ticket_url);
    $event->set_categories($categories);
}

// Format the info of each event into an arguments array that's used for the set-event.php script.
$eventsArgs = [];
foreach($events as $i => $event) {
    $eventsArgs[] = [
        'event' => $event->to_args(),
        'venue' => $venues[$i]->to_args()
    ];
}

echo json_encode($eventsArgs);

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
$eventCreationRequest = set_event($eventCreationArgs);
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

// Given an events page, find and extract the date & time of the event.
function extract_fb_event_datetime($dom) {
    $finder = new DomXPath($dom);
    if (is_recurring_event($dom)) {
        // Extract the datetime from the 'Upcoming Events' section of the page.
        $nodes = $finder->query('//div[contains(text(), "Upcoming Dates")]');
        $firstDatetime = $nodes->item(0)->nextSibling->firstChild->firstChild->firstChild->firstChild->firstChild;
        $date = $firstDatetime->firstChild->firstChild->getAttribute("title");
        $day_and_time = $firstDatetime->childNodes->item(1)->firstChild->textContent;
        $day_and_time = substr($day_and_time, 4); // Trim off the shortened day (e.g., trim off the 'THU' in 'THU 5:00 PM - 7:30 PM')
        return $date . " at " . $day_and_time;
    }
    else {
        // Extract the datetime from the hero section underneath the event image.
        $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/yL/r/HvJ9U6sdYns.png';
        $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
        return $nodes->item(0)->parentNode->parentNode->textContent;
    }
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
    $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/y_/r/_gA751gYiTQ.png'; // Location pin icon
    $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
    if ($nodes->count() <= 0) {
        // If the location pin icon was not found, then the event occurs online and has a url.
        $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/yq/r/rgRA6qanUH1.png'; // World icon
        $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
    }
    $addressElem = $nodes->item(0)->parentNode->parentNode->getElementsByTagName('dd');
    return $addressElem->item(0)->textContent ?? "";
}

// Given a location extracted from the event page, extract the street name and number.
function street_from_location($location) {
    return trim(explode(",", $location)[0]);
}

// Given a location extracted from the event page, extract the city name.
function city_from_location($location) {
    return trim(explode(",", $location)[1]);
}

// Given a location extracted from the event page, extract the state name.
function state_from_location($location) {
    $stateAndZip = trim(explode(",", $location)[2]); // State is on the left of the space, zipcode is on the right of the space.
    return explode(" ", $stateAndZip)[0];
}

// Given a location extracted from the event page, extract the zip code.
function zip_from_location($location) {
    $stateAndZip = trim(explode(",", $location)[2]); // State is on the left of the space, zipcode is on the right of the space.
    return explode(" ", $stateAndZip)[1];
}

function location_is_online($location) {
    return str_contains($location, "http");
}

// Given an events page, find and extract the organization running the event.
function extract_fb_event_organization($dom) {
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//div[contains(text(), '·')]"); // The element which contains the organization name is the only one with '·' in its text.
    return $nodes->item(0)->getElementsByTagName('a')->item(0)->textContent; // Find the <a> tag in this container, then extract its text.
}

// Given an events page, find and extract the url to the organization's facebook page.
function extract_fb_event_organization_url($dom) {
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//div[contains(text(), '·')]"); // The element which contains the organization name is the only one with '·' in its text.
    return $nodes->item(0)->getElementsByTagName('a')->item(0)->getAttribute("href"); // Find the <a> tag in this container, then extract the url.
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
    // Different dashes are used on different pages. This if statement guarantees we detect the dash in the string if it exists.
    if (str_contains($times, " – ")) {
        // First dash type has been detected
        return explode(" – ", $times)[0];
    }
    else if (str_contains($times, " - ")) {
        // Second dash type has been detected
        return explode(" - ", $times)[0];
    }
    else {
        // No ending time specified.
        return $times;
    }
}

// Extracts the end time from a date time string obtained from the 'extract_fb_event_datetime' function.
function end_date_from_datetime($datetime) {
    return explode(" at ", $datetime)[0];
}

// Extracts the end time from a date time string obtained from the 'extract_fb_event_datetime' function.
function end_time_from_datetime($datetime) {
    $timeStr = explode(" at ", $datetime)[1];
    // Different dashes are used on different pages. This if statement guarantees we detect the dash in the string if it exists.
    if (str_contains($timeStr, " – ")) {
        // First dash type has been detected
        return explode(" – ", $timeStr)[1];
    }
    else if (str_contains($timeStr, " - ")) {
        // Second dash type has been detected
        return explode(" - ", $timeStr)[1];
    }
    else {
        // No ending time specified.
        return $timeStr;
    }
}

// Check if an event occurs on different days.
function is_recurring_event($dom) {
    $finder = new DomXPath($dom);
    $nodes = $finder->query('//div[contains(text(), "Upcoming Dates")]');
    return $nodes->count() > 0;
}

// Requires the normal event page rather than the mbasic event page
function extract_fb_event_ticket_url($dom) {
    $finder = new DomXPath($dom);
    $nodes = $finder->query('//i[contains(@style, \'background-image: url("https://static.xx.fbcdn.net/rsrc.php/v3/y9/r/ffPDAWslkC5.png"); background-position: -44px -246px; background-size: 190px 322px;\')]');
    if ($nodes->count() > 0) {
        // This event has tickets that can be purchased.
        return $nodes->item(0)->parentNode->nextSibling->firstChild->lastChild->textContent;
    }
    else {
        return "";
    }
}

// Given an events page, find and extract the location title for where the event is hosted.
function extract_fb_location_title($dom) {
    $finder = new DomXPath($dom);
    $imageSrc = 'https://static.xx.fbcdn.net/rsrc.php/v3/y_/r/_gA751gYiTQ.png'; // Location pin icon
    $nodes = $finder->query("//img[contains(@src, '$imageSrc')]");
    if ($nodes->count() > 0) {
        $addressElem = $nodes->item(0)->parentNode->parentNode->getElementsByTagName('dt');
        return $addressElem->item(0)->textContent ?? "";
    }
    else {
        return "";
    }
}

// check if this event has any categories. This requires the normal event page rather than the mbasic event page.
function extract_fb_event_categories($dom) {
    $finder = new DomXPath($dom);
    $nodes = $finder->query('//a[contains(@href, \'/events/discovery\')]');
    $categories = [];
    for ($i = 0, $len = $nodes->length; $i < $len; $i++) {
        $categories[] = $nodes->item($i)->textContent;
    }
    return $categories;
}
?>