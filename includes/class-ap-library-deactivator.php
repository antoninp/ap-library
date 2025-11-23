<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		// Unregister custom post type
		unregister_post_type( 'aplb_photo' );

		// Unregister taxonomies
		unregister_taxonomy( 'aplb_taken_date' );
		unregister_taxonomy( 'aplb_genre' );
		unregister_taxonomy( 'aplb_published_date' );
		unregister_taxonomy( 'aplb_keyword' );
		// Old taxonomies removed in consolidation; no-op if missing

		// Flush rewrite rules to remove CPT/taxonomy permalinks
		flush_rewrite_rules();
	}

}
