<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://nicksabia.tech/
 * @since      0.0.1
 *
 * @package    Glorious_Scraper
 * @subpackage Glorious_Scraper/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @todo Justify why we need this or remove it. AFAIK nothing can be done with textdomains else than loading it.
 *       This, if true, makes this class a total waste of code.
 *
 * @since      0.0.1
 * @package    Glorious_Scraper
 * @subpackage Glorious_Scraper/includes
 * @author     Your Name <lockdownwizards@gmail.com>
 */
class Glorious_Scraper_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'glorious-scraper',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
