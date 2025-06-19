<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    AP_Library
 * @subpackage AP_Library/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    AP_Library
 * @subpackage AP_Library/includes
 * @author     Antonin Puleo
 */
class AP_Library_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Unregister the custom post type
        self::unregister_custom_post_type();

        // Flush rewrite rules to remove the post type from the structure
        flush_rewrite_rules();

	}

    public static function unregister_custom_post_type() {
        unregister_post_type( 'aplb_uploads' );
		unregister_post_type( 'aplb_library' );

    }

}
