<?php

/**
 * Fired during plugin activation
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library_Activator {

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

        // Register the option with default value 'false'
        add_option( 'ap_library_auto_create_post_on_upload', false );
	}

}
