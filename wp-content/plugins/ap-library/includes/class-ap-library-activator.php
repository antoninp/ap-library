<?php

/**
 * Fired during plugin activation
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    AP_Library
 * @subpackage AP_Library/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AP_Library
 * @subpackage AP_Library/includes
 * @author     Antonin Puleo
 */
class AP_Library_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        // Flush rewrite rules to apply changes
        flush_rewrite_rules();

	}

}
