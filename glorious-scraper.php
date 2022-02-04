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


	// SELECT * FROM wp_gr_scraper_urls and store in $urls
	// $urls = $wpdb->get_results("SELECT * FROM wp_gr_scraper_urls");
	// $wpdb->get_results("SELECT * FROM {$wpdb->prefix}author_followers WHERE author_id = $author_id", OBJECT);
	$urls = $wpdb->get_results("SELECT * FROM wp_gr_scraper_urls");
	//error_log($urls);
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
			<button>Run Scraper</button>
		</section>
		<section>
			<h2>Settings</h2>
			<form>
				<h3>Scheduled scrape time</h3>
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
					<th>Action</th>
				</tr>
				<?php
				//json_encode($urls);

				//var_dump(get_object_vars($urls))

				foreach ($urls as $url) {
					url_table_entry($url);
				}
				?>

				<form method="POST" action="../wp-content/plugins/glorious-scraper/add-url.php">
					<tr>
						<td class="full-width">
							<input value="" placeholder="Add new URL..." name="new" />
						</td>
						<td><input type='submit' href="JavaScript:void(0);" value="Add URL" /></td>
					</tr>
				</form>
			</table>

			<form method="POST" action="save-all.php">
				<input type='submit' href="JavaScript:void(0);" value="Save Settings" />
			</form>
		</section>
	</div>
<?php
}

function url_table_entry($url)
{
?><tr>
		<td class="full-width"><input value="<?php echo $url->url; ?>" /></td>
		<td>
			<form method="post" action="../wp-content/plugins/glorious-scraper/delete.php">
				<input style="display: none;" value="<?php echo $url->id; ?>" name="delete" />
				<input type="submit" value="delete" id="<?php echo $url->id; ?>" class="delete-button" />
			</form>
		</td>
	</tr><?php
		}

		function update_urls()
		{
			global $urls;
			global $wpdb;

			// loop through urls and update each one with the current input value
			foreach ($urls as $url) {
				$wpdb->update(
					'wp_gr_scraper_urls',
					array(
						'url' => $_POST[$url->id]
					),
					array(
						'id' => $url->id
					)
				);
			}
		}

		plugin_name_run();

			?>