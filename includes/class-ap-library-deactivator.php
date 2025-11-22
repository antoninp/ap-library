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
		
		// Unregister custom post types (library CPT removed)
		unregister_post_type( 'aplb_uploads' );

		// Unregister taxonomies if needed
		unregister_taxonomy( 'aplb_uploads_tdate' );
		unregister_taxonomy( 'aplb_uploads_genre' );
		unregister_taxonomy( 'aplb_library_pdate' );
		unregister_taxonomy( 'aplb_uploads_keyword' );

		// Flush rewrite rules to remove CPT/taxonomy permalinks
		flush_rewrite_rules();
	}

}
