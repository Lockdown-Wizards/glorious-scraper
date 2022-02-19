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

	// Grab all group URL's from database.
	$table_name = $wpdb->prefix . "gr_fbgroups";
	$urls = $wpdb->get_results("SELECT * FROM $table_name");

	$plugin->run();
}

function setup_admin_menu()
{
	add_menu_page('Event Scraper', 'Event Scraper', 'manage_options', 'event-scraper', 'admin_menu_init');
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
								echo $organization_opt ? $organization_opt : '';
								?>" placeholder="Organization Name" name="organization" />
				<input type='submit' href="JavaScript:void(0);" value="Set Organization" />
			</form>
			<h3>Scheduled Scrape Time</h3>
			<form>
				<div>
					<span>
						<label for='hours'>Hour:</label>
						<select id='hours'>
							<option value='00'>00</option>
							<option value='01'>01</option>
							<option value='02'>02</option>
							<option value='03'>03</option>
							<option value='04'>04</option>
							<option value='05'>05</option>
							<option value='06'>06</option>
							<option value='07'>07</option>
							<option value='08'>08</option>
							<option value='09'>09</option>
							<option value='10'>10</option>
							<option value='11'>11</option>
							<option value='12'>12</option>
							<option value='13'>13</option>
							<option value='14'>14</option>
							<option value='15'>15</option>
							<option value='16'>16</option>
							<option value='17'>17</option>
							<option value='18'>18</option>
							<option value='19'>19</option>
							<option value='20'>20</option>
							<option value='21'>21</option>
							<option value='22'>22</option>
							<option value='23'>23</option>
						</select>
					</span>
					<span>
						<label for='minutes'>Minute:</label>
						<select id='minutes'>
							<option value='00'>00</option>
							<option value='05'>05</option>
							<option value='10'>10</option>
							<option value='15'>15</option>
							<option value='20'>20</option>
							<option value='25'>25</option>
							<option value='30'>30</option>
							<option value='35'>35</option>
							<option value='40'>40</option>
							<option value='45'>45</option>
							<option value='50'>50</option>
							<option value='55'>55</option>
						</select>
					</span>
				</div>
				<br>
				<input type='submit' value='Save Settings' />
			</form>

			<h3>Saved URLs</h3>
			<table id="urls-table">
				<tr>
					<th>URL</th>
				</tr>
				<?php
				//json_encode($urls);

				//var_dump(get_object_vars($urls))

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
	<script>
		let scraperConsole = document.getElementById("scraperConsole");
		let scraperButton = document.getElementById("scraperButton");

		<?php
		if (is_plugin_active('the-events-calendar/the-events-calendar.php')) {
		?>
			/*
				Takes the message in the form:
				[{"id":"530192818301298","post_title":"CSO Gala ~ An Enchanted Evening","EventURL":"https:\/\/www.facebook.com\/events\/530192818301298","post_content":"Event by: Community Speaks Out<\/a><\/i>\nWe have a new date and venue! July 30th at the Mashantucket Pequot Museum and Research Center. This is a major fundraiser for our organization. The Gala is a night of dinner, dancing, online auction and presentations about CSO. We have sponsorship opportunities and welcome auction item and gift card donations. Purchase your tickets before May 1st and your name will be entered into a drawing for $100 gift certificate to Dog Watch Cafe! For more information about the event and to purchase tickets: https:\/\/CSOGala2022.givesmart.com\n\nFor directions to this event, please click here.<\/a><\/b>\nTo view this event on Facebook, please click here.<\/a><\/b>","post_type":"tribe_events","EventStartDate":"Saturday, July 30, 2022","EventEndDate":"Saturday, July 30, 2022","EventStartHour":"06","EventStartMinute":"00","EventStartMeridian":"PM","EventEndHour":"11","EventEndMinute":"00","EventEndMeridian":"PM","FeaturedImage":"https:\/\/scontent-bos3-1.xx.fbcdn.net\/v\/t39.30808-6\/fr\/cp0\/e15\/q65\/273936784_5078627658825358_4568355614301083006_n.jpg?_nc_cat=111&ccb=1-5&_nc_sid=ed5ff1&_nc_ohc=W5XlDn8_p4sAX9_3sAR&_nc_ht=scontent-bos3-1.xx&oh=00_AT_iksrXS3k8AB7OJdzmNnpfAbutWn_VG_cQCp3pMvGFeA&oe=6216B109","Organizer":"Community Speaks Out","post_category":[],"Venue":"Mashantucket Pequot Museum & Research Center","comment_status":"open"}]

				Extracts the post_title and returns "Draft set for <post_title>"
			*/
			function formatMessage(message) {
				let json = JSON.parse(message);
				let post_title = json[0].post_title;
				return "Draft set for " + post_title;
			}

			// AJAX call to url-feeder.php to handle the scraping of all urls,
			// then return the result back.
			scraperButton.addEventListener("click", (e) => {
				writeToConsole("Now scraping for facebook events. This may take a while, so hang tight and make a cup of tea!");
				// <?php $urls; ?>
				// call url-feeder.php for each url. Thr urls are stored with the class "table-url-value"
				let urls = document.getElementsByClassName("table-url-value");
				let urlsArray = [];
				for (let i = 0; i < urls.length; i++) {
					urlsArray.push(urls[i].value);
				}
				for (let i = 0; i < urlsArray.length; i++) {
					console.log(urlsArray[i]);
					writeToConsole("Now scraping " + urlsArray[i] + " (" + (i + 1) + " of " + urlsArray.length + ")" + "...");

					let request = new XMLHttpRequest();
					request.open("POST", "../wp-content/plugins/glorious-scraper/url-feeder.php", false); // false for synchronous request, true for asynchronous
					request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					request.send("url=" + urlsArray[i]);

					if (request.status === 200) {
						let response = request.responseText;
						let message = formatMessage(response);
						writeToConsole(message);
					} else {
						writeToConsole("Error: " + request.status);
					}
				}
				writeToConsole("Scraping complete! Have a great day!");
			});
		<?php
		} else {
		?>
			// Display the call to action for installing the event calendar.
			let eventCalendarErrorElem = document.getElementById("eventCalendarErrorMsg");
			eventCalendarErrorElem.className = "";
			scraperButton.disabled = true;
		<?php
		}
		?>

		// scraperConsole is a global
		function writeToConsole(msg) {
			let messageElem = document.createElement("div");
			messageElem.className = "scraper-console-line";
			messageElem.innerHTML = msg;
			scraperConsole.append(messageElem);
		}
	</script>
<?php
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