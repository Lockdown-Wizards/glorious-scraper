<?php
// Access the wordpress database
require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
global $wpdb;

// Load in the Requests library: https://github.com/WordPress/Requests
require_once dirname(__DIR__) . '\\glorious-scraper\\requests\\src\\Autoload.php'; // First, include the Requests Autoloader.
WpOrg\Requests\Autoload::register(); // Next, make sure Requests can load internal classes.

// Load the events calendar event creation API
require_once dirname(__DIR__) . '\\the-events-calendar\\src\\functions\\php-min-version.php'; // Load the required php min version functions.
require_once dirname(__DIR__) . '\\the-events-calendar\\vendor\\autoload.php'; // Load the Composer autoload file.
require_once dirname(__DIR__) . '\\the-events-calendar\\src\\Tribe\\Main.php'; // Loads 'the events calendar' main
require_once dirname(__DIR__) . '\\the-events-calendar\\src\\Tribe\\API.php'; // Loads 'the events calendar' api main
Tribe__Events__Main::instance(); // Create an instance of 'the events calendar' main singleton
require_once dirname(__DIR__) . '\\the-events-calendar\\src\\functions\\advanced-functions\\event.php'; // Load the script needed to create events.

if (isset($_POST['args'])) {
    // Documentation for all args: https://docs.theeventscalendar.com/reference/functions/tribe_create_event/
    $args = $_POST['args'];

    // Since it's possible to enter in the wrong ID key in the args array, this code ensures that the ID key will be 'ID' if the post already exists or 'import_id' if the post doesn't yet exist.
    $postId = $args['ID'] || $args['id'] || $args['import_id'];
    $postIdArg = (get_post_status($postId)) ? 'ID' : 'import_id'; // https://stackoverflow.com/questions/41655064/why-wp-update-post-return-invalid-post-id
    unset($args['ID']);
    unset($args['id']);
    unset($args['import_id']);
    $updatedArgs = [$postIdArg => $postId];
    foreach ($args as $key => $arg) {
        if ($arg !== null) {
            $updatedArgs[$key] = $arg;
        }
    }
    // [$postId => 12345678, "post_title" => "THIS IS AN OBVIOUS TEST SO JUST LOOK FOR THIS TEXT IN THE DB PLEASE", "EventStartDate" => "2022-08-28 07:00:00", "EventEndDate" => "2022-08-28 09:00:00"]
    var_dump(tribe_create_event($updatedArgs));
    //var_dump($updatedArgs);
}
else {
    echo 'Args not supplied to the create-event script. Cannot add event to the database. Did you forget to add [\'args\' => $args] as your post request body?';
}

?>