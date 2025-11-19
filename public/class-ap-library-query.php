<?php

/**
 * Query modifications for archive ordering.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/public
 */

/**
 * Handles WP_Query modifications for archives.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/public
 * @author     Antonin Puleo
 */
class Ap_Library_Query {

	/**
	 * Modify archive queries to order by published_date meta.
	 *
	 * @since    1.0.0
	 * @param    WP_Query    $query    The WP_Query instance.
	 */
	public function modify_archive_query( $query ) {
		// Only modify main query on frontend archives
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Check if this is a genre or taken date taxonomy archive
		$is_genre_archive = $query->is_tax( 'aplb_uploads_genre' );
		$is_tdate_archive = $query->is_tax( 'aplb_uploads_tdate' );
		$is_pdate_archive = $query->is_tax( 'aplb_library_pdate' );
		$is_uploads_archive = $query->is_post_type_archive( 'aplb_uploads' );

		// Apply ordering for relevant archives
		if ( $is_genre_archive || $is_tdate_archive ) {
			// Set post type for taxonomy archives (WordPress doesn't do this automatically)
			$query->set( 'post_type', 'aplb_uploads' );
			// Ensure we only show published posts
			$query->set( 'post_status', 'publish' );
			// Order by published date (most recent first)
			$query->set( 'meta_key', APLB_META_PUBLISHED_DATE );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'DESC' );
		} elseif ( $is_uploads_archive ) {
			// Post type already set for post type archives
			$query->set( 'post_status', 'publish' );
			$query->set( 'meta_key', APLB_META_PUBLISHED_DATE );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'DESC' );
		} elseif ( $is_pdate_archive ) {
			// Published date taxonomy is shared between aplb_library and aplb_uploads
			$query->set( 'post_type', array( 'aplb_library', 'aplb_uploads' ) );
			$query->set( 'post_status', 'publish' );
			// For published date archives, order by post date
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}
	}

	/**
	 * Allow orderby via query vars for Query Loop block.
	 *
	 * @since    1.0.0
	 * @param    array    $query_vars    Current public query vars.
	 * @return   array    Modified query vars.
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'aplb_orderby';
		return $query_vars;
	}
}
