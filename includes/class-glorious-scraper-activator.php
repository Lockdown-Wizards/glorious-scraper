<?php
/**
 * Fired during plugin activation
 *
 * @link       https://nicksabia.tech/
 * @since      0.0.1
 *
 * @package    Glorious_Scraper
 * @subpackage Glorious_Scraper/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @todo This should probably be in one class together with Deactivator Class.
 * @since      0.0.1
 * @package    Glorious_Scraper
 * @subpackage Glorious_Scraper/includes
 * @author     Your Name <lockdownwizards@gmail.com>
 */

// Access to the Wordpress database
//require_once '../../../../wp-load.php';
//global $wpdb;

class Glorious_Scraper_Activator {

	/**
	 * The $_REQUEST during plugin activation.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      array    $request    The $_REQUEST array during plugin activation.
	 */
	private static $request = array();

	/**
	 * The $_REQUEST['plugin'] during plugin activation.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin    The $_REQUEST['plugin'] value during plugin activation.
	 */
	private static $plugin  = 'glorious-scraper/glorious-scraper.php';

	/**
	 * The $_REQUEST['action'] during plugin activation.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      array    $action    The $_REQUEST[action] value during plugin activation.
	 */
	private static $action  = 'activate';

	/**
	 * Activate the plugin.
	 *
	 * Checks if the plugin was (safely) activated.
	 * Place to add any custom action during plugin activation.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {

		if ( false === self::get_request()
			|| false === self::validate_request( self::$plugin )
			|| false === self::check_caps()
		) {
			if ( isset( $_REQUEST['plugin'] ) ) {
				if ( ! check_admin_referer( 'activate-plugin_' . self::$request['plugin'] ) ) {
					exit;
				}
			} elseif ( isset( $_REQUEST['checked'] ) ) {
				if ( ! check_admin_referer( 'bulk-plugins' ) ) {
					exit;
				}
			}
		}

		/**
		 * The plugin is now safely activated.
		 * Perform your activation actions here.
		 */
		install_fbgroups_table();
		install_events_table();
	}

	/**
	 * Get the request.
	 *
	 * Gets the $_REQUEST array and checks if necessary keys are set.
	 * Populates self::request with necessary and sanitized values.
	 *
	 * @since    0.0.1
	 * @return bool|array false or self::$request array.
	 */
	private static function get_request() {

		if ( ! empty( $_REQUEST )
			&& isset( $_REQUEST['_wpnonce'] )
			&& isset( $_REQUEST['action'] )
		) {
			if ( isset( $_REQUEST['plugin'] ) ) {
				if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'activate-plugin_' . sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) ) ) {

					self::$request['plugin'] = sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) );
					self::$request['action'] = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );

					return self::$request;

				}
			} elseif ( isset( $_REQUEST['checked'] ) ) {
				if ( false !== wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-plugins' ) ) {

					self::$request['action'] = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
					self::$request['plugins'] = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['checked'] ) );

					return self::$request;

				}
			}
		} else {

			return false;
		}

	}

	/**
	 * Validate the Request data.
	 *
	 * Validates the $_REQUESTed data is matching this plugin and action.
	 *
	 * @since    0.0.1
	 * @param string $plugin The Plugin folder/name.php.
	 * @return bool false if either plugin or action does not match, else true.
	 */
	private static function validate_request( $plugin ) {

		if ( $plugin === self::$request['plugin']
			&& 'activate' === self::$request['action']
		) {

			return true;

		} elseif ( 'activate-selected' === self::$request['action']
			&& in_array( $plugin, self::$request['plugins'] )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Check Capabilities.
	 *
	 * We want no one else but users with activate_plugins or above to be able to active this plugin.
	 *
	 * @since    0.0.1
	 * @return bool false if no caps, else true.
	 */
	private static function check_caps() {

		if ( current_user_can( 'activate_plugins' ) ) {
			return true;
		}

		return false;

	}

}

// Creates the table in the db responsible for holding the group urls to scrape for events.
function install_fbgroups_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'gr_fbgroups';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int(10) unsigned NOT NULL AUTO_INCREMENT,
		url text NOT NULL,
		active tinyint(1) NOT NULL,
		PRIMARY KEY  (id)
	  ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	/*$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );*/

	//add_option( 'jal_db_version', $jal_db_version );
}

// Creates the table in the db responsible for holding the group urls to scrape for events.
function install_events_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'gr_events';
	$group_table_name = $wpdb->prefix . 'gr_fbgroups';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		`id` int(10) unsigned NOT NULL COMMENT 'facebook id',
		`gid` int(10) unsigned NOT NULL COMMENT 'facebook group id',
		`wpid` bigint(20) unsigned NOT NULL COMMENT 'wp post id',
		`last_scraped` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
		PRIMARY KEY (`id`),
		KEY `wp_gr_events_ibfk_1` (`gid`),
		KEY `wpid` (`wpid`),
		CONSTRAINT `wp_gr_events_ibfk_1` FOREIGN KEY (`gid`) REFERENCES $group_table_name (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `wp_gr_events_ibfk_2` FOREIGN KEY (`wpid`) REFERENCES `wp_posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
	  ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}