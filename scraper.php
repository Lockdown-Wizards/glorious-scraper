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
require_once dirname(__DIR__) . '\\glorious-scraper\\\requests\\src\\Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

use WpOrg\Requests\Session;

const LOGIN_URL = "https://www.facebook.com/login/";

// Now let's make a request!
//$request = WpOrg\Requests\Requests::get('http://httpbin.org/get', ['Accept' => 'application/json']);
$url = $_POST['url'];
//$request = WpOrg\Requests\Requests::get($url, ['Accept' => 'application/json']);
$fbSession = new Session();
$page = $fbSession->request($url);
//$decodedBody = $page->decode_body();
//var_dump($decodedBody);
$dom = new DOMDocument(); // Create a new DOMDocument object which will be used for parsing through the html
@ $dom->loadHTML($page->body); // @ surpresses any warnings

$eventLinks = extract_fb_event_links($dom);



// Check what we received
//var_dump($request);
//echo json_encode($page);
//echo $page->body;
var_dump($hrefs);

// Given a facebook page, find and extract facebook event links.
// Returns an array of links.
function extract_fb_event_links($dom) {
    // Grab all <a> tags from the event page.
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
?>