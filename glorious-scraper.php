<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress or ClassicPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/Lockdown-Wizards/glorious-scraper
 * @since             0.0.1
 * @package           Glorious_Scraper
 *
 * @wordpress-plugin
 * Plugin Name:       Glorious Scraper
 * Plugin URI:        https://plugin.com/glorious-scraper-uri/
 * Description:       An event scraper which runs daily, pulling info from Facebook.
 * Version:           0.0.1
 * Author:            Lockdown Wizards
 * Requires at least: 5.9
 * Tested up to:      5.8
 * Author URI:        https://github.com/Lockdown-Wizards/glorious-scraper
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       glorious-scraper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Current plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('GLORIOUS_SCRAPER_VERSION', '0.0.1');

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-glorious-scraper-activator.php
 * Full security checks are performed inside the class.
 */
function plugin_name_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-glorious-scraper-activator.php';
	Glorious_Scraper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-glorious-scraper-deactivator.php
 * Full security checks are performed inside the class.
 */
function plugin_name_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-glorious-scraper-deactivator.php';
	Glorious_Scraper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'plugin_name_activate');
register_deactivation_hook(__FILE__, 'plugin_name_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-glorious-scraper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Generally you will want to hook this function, instead of calling it globally.
 * However since the purpose of your plugin is not known until you write it, we include the function globally.
 *
 * @since    0.0.1
 */
function plugin_name_run()
{
	global $wpdb;
	global $urls;
	$plugin = new Glorious_Scraper();
	//$plugin->get_loader()->add_action('admin_menu', $plugin, 'setup_admin_menu');
	add_action('admin_menu', 'setup_admin_menu');

	// For cron job
	add_action( 'gloriousrecovery_cronjob_hook', 'gr_cronjob' );

	// Grab all group URL's from database.
	$table_name = $wpdb->prefix . "gr_fbgroups";
	$urls = $wpdb->get_results("SELECT * FROM $table_name");

	// Pushes the URL data obtained from the database to scrape-all.js
	add_action('admin_enqueue_scripts', 'localize_urls');

	$plugin->run();
}

function setup_admin_menu()
{
	add_menu_page('Event Scraper', 'Event Scraper', 'manage_options', 'event-scraper', 'admin_menu_init');
}

// Takes the url data obtained in this file and transfers it to the frontend (scrape-all.js) in the gloriousData variable.
function localize_urls() {
	global $urls;
	wp_enqueue_script('glorious_scraper', plugin_dir_url( __FILE__ ) . 'scrape-all.js');
	wp_localize_script('glorious_scraper', 'gloriousData', ['urls' => $urls]);
}


function gr_cronjob() {
	// This should be doing the actual cronjob
	global $urls;

	if ($urls[0] !== null) {
		// loop through all the urls and make a request for each one.
		foreach ($urls as $url) {
			error_log(json_encode("Now scraping: " . $url->url));
			
			// testing?
			$request = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $url]);
			
			// original
			// $request = WpOrg\Requests\Requests::post(get_site_url() . "/wp-content/plugins/glorious-scraper/scraper.php", [], ['url' => $url->url]);
			
			
			// If the request was successful, then echo the request's body.
			if ($request->status_code === 200) {
				echo $request->body;
				// error_log(json_encode($request->body)); // Uncomment this line to see the request's body.
	
				// Add each event to 'the events calendar' plugin.
				$eventsArgs = json_decode($request->body);
				$actionsTaken = ""; // Shows what events have been saved as drafts in 'the events calendar' plugin.
				foreach ($eventsArgs as $args) {
					$postId = set_event($args);
					$actionsTaken .= "Draft set for '" . $args->post_title . "' with event id: " . $args->id . "\n";
					
					error_log(json_encode($actionsTaken));
					echo json_encode($actionsTaken);
				}
				// echo json_encode($actionsTaken);
			}
			error_log("done");
		}
	}
}


function admin_menu_init()
{
	global $urls;
?>
	<div id="wrapper-event-scraper">
		<h1>Event Scraper</h1>
		<section>
			<h2>Actions</h2>
			<div id="scraperConsole">
				<ul id="scraperConsoleUl">
				</ul>
			</div>
			<br>
			<button id="scraperButton">Run Scraper</button>
			<span id="eventCalendarErrorMsg" class="hidden" style="color:red; margin-left:10px;">Unable to run the scraper. You must install <a href="https://theeventscalendar.com/">The Events Calendar</a> plugin first, then try again.</span>
		</section>

		<section>
			<h2>Settings</h2>
			<h3>Organization Name</h3>
			<p>When an event with this organization name is found, the scraper will automatically feature the event.</p>
			<form method="POST" action="../wp-content/plugins/glorious-scraper/set-organization.php">
				<input value="<?php
								// On load, check if an organization has been entered. If so, autofill the input box.
								$organization_opt = get_option('scraper_organization_name');
								$organization_opt ? $organization_opt : '';
								?>" placeholder="Organization Name" name="organization" />
				<input type='submit' href="JavaScript:void(0);" value="Set Organization" />
			</form>

			<h3>Scheduled Scrape Time</h3>
			<form method="POST" action="../wp-content/plugins/glorious-scraper/set-cronjob.php">
				<?php  
					// False for not scheduled, otherwise returns timestamp
					$gr_next_cronjob = wp_next_scheduled( 'gloriousrecovery_cronjob_hook' );
					// echo $gr_next_cronjob . " " . gettype($gr_next_cronjob) ."<br>";
					
					// Used for Radio button section (cronjob recurrences)
					$gr_cronjob_current_schedule = wp_get_schedule('gloriousrecovery_cronjob_hook');	

					if($gr_next_cronjob) {
						$gr_timezone_here = new DateTimeZone('America/New_York');
						$gr_timezone_GMT = new DateTimeZone("Europe/London");
						$gr_datetime_here = new DateTime("now", $gr_timezone_here);
						$gr_datetime_GMT = new DateTime("now", $gr_timezone_GMT);
						// echo "Now here: " . $gr_datetime_here->format('Y-m-d H:i:s') . "<br>";
						// echo "Now GMT:  " . $gr_datetime_GMT->format('Y-m-d H:i:s') . "<br>";

						$gr_datetime_offset = timezone_offset_get($gr_timezone_here, $gr_datetime_GMT );
						//echo "offset:  " . $gr_datetime_offset . "<br>";
						$gr_next_cronjob += $gr_datetime_offset;
						
						$gr_current_hour_24h = floor(($gr_next_cronjob % 86400)/(3600));
						$gr_current_hour = $gr_cronjob_current_schedule == 'twicedaily' ? $gr_current_hour_24h % 12 : $gr_current_hour_24h ; // hours after midnight
						$gr_current_minutes = floor(($gr_next_cronjob % 3600)/(60)); // minutes after the hour

						// echo $gr_current_hour ."<br>";
						// echo $gr_current_minutes ."<br>";

					}
					else {
						$gr_current_hour =  0;
						$gr_current_minutes = 0;
					}
				?>
				<div>
					<span>
						<label for='hours'>Hour:</label>
						<select name='hours' id='hours'>
						<?php
							for ($i = 0; $i <= 23; $i++) {
								if($i == (int) $gr_current_hour) {
									if ($i < 10) {
										echo "<option value='0$i' selected>0$i</option>";
									}
									else {
										echo "<option value='$i' selected>$i</option>";
									}
								}
								else {
									if ($i < 10) {
										echo "<option value='0$i'>0$i</option>";
									}
									else {
										echo "<option value='$i'>$i</option>";
									}
								}
							}
							?>
						</select>
					</span>
					<span>
						<label for='minutes'>Minute:</label>
						<select name='minutes' id='minutes'>

						<?php
							for ($i = 0; $i <= 11; $i++) {
								$j = $i * 5;

								// Make sure it is selected
								if($j == (int) $gr_current_minutes) {
									if ($j < 10)
										echo "<option value='0$j' selected>0$j</option>";
									else 
										echo "<option value='$j' selected>$j</option>";
								}
								else {
									if ($j < 10)
										echo "<option value='0$j'>0$j</option>";
									else 
										echo "<option value='$j'>$j</option>";
								}
							}
							?>
						</select>
					</span>
					<!-- Need to check which is selected to begin... -->
					<br>
					<br>
					<span> 
						<input type="radio" id="cronChoice1" name="cronjobRecurrence" value="none" <?php if(!$gr_cronjob_current_schedule) echo "checked" ?>>
						<label for="cronChoice1">None</label>

						<input type="radio" id="cronChoice2" name="cronjobRecurrence" value="daily" <?php if($gr_cronjob_current_schedule == 'daily') echo "checked" ?>>
						<label for="cronChoice2">Daily</label>

						<input type="radio" id="cronChoice3" name="cronjobRecurrence" value="twicedaily" <?php if($gr_cronjob_current_schedule == 'twicedaily') echo "checked" ?>>
						<label for="cronChoice3">Twice Daily</label>
					</span>
				</div>
				<br>
				<input type='submit' href="JavaScript:void(0);" value='Save Settings' />
			</form>

			<h3>Saved URLs</h3>
			<table id="urls-table">
				<tr>
					<th>URL</th>
				</tr>
				<?php
					foreach ($urls as $url) {
						url_table_entry($url);
					}
				?>
				<tr>
					<td class="full-width">
						<form method="POST" action="../wp-content/plugins/glorious-scraper/add-url.php">
							<input class="full-width" value="" placeholder="Add new URL..." name="new" />
							<input type='submit' href="JavaScript:void(0);" value="Add URL" />
						</form>
					</td>
				</tr>
			</table>
		</section>
	</div>
	<?php
	if (!is_plugin_active('the-events-calendar/the-events-calendar.php')) {
	?>
		<!--<script src="../wp-content/plugins/glorious-scraper/scrape-all.js"></script>-->
		<script src="../wp-content/plugins/glorious-scraper/missing-events-calendar.js"></script>
	<?php
	}
}

function url_table_entry($url)
{
?><tr>
	<td>
		<form method="post" action="../wp-content/plugins/glorious-scraper/save-all.php">
			<input class="full-width table-url-value" value="<?php echo $url->url; ?>" name="url" id="url-table-<?php echo $url->id; ?>" />
			<input style="display: none;" value="<?php echo $url->id; ?>" name="id" />
			<input type="submit" value="Update" />
		</form>
		<form method="post" class="left" action="../wp-content/plugins/glorious-scraper/delete.php">
			<input style="display: none; float: left;" class="left" value="<?php echo $url->id; ?>" name="delete" />
			<input type="submit" value="delete" class="left" style="float: left;" id="<?php echo $url->id; ?>" class="delete-button" />
		</form>
	</td>
</tr><?php
}

plugin_name_run();

?>