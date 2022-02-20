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