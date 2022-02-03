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
 * Description:       An event scraper which runs daily, pulling into from Facebook.
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
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GLORIOUS_SCRAPER_VERSION', '0.0.1' );

/**
 * The code that runs during plugin activation.
 *
 * This action is documented in includes/class-glorious-scraper-activator.php
 * Full security checks are performed inside the class.
 */
function plugin_name_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-glorious-scraper-activator.php';
	Glorious_Scraper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * This action is documented in includes/class-glorious-scraper-deactivator.php
 * Full security checks are performed inside the class.
 */
function plugin_name_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-glorious-scraper-deactivator.php';
	Glorious_Scraper_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'plugin_name_activate' );
register_deactivation_hook( __FILE__, 'plugin_name_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-glorious-scraper.php';

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
function plugin_name_run() {

	$plugin = new Glorious_Scraper();
	//$plugin->get_loader()->add_action('admin_menu', $plugin, 'setup_admin_menu');
	add_action('admin_menu', 'setup_admin_menu');
	$plugin->run();
}

function setup_admin_menu(){
    add_menu_page( 'Event Scraper', 'Event Scraper', 'manage_options', 'event-scraper', 'admin_menu_init' );
}
 
function admin_menu_init(){
    echo "<h1>Hello World!</h1>";
}
plugin_name_run();
