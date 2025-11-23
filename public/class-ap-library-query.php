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
	 * Modify archive queries using configurable rules.
	 *
	 * @since    1.0.0
	 * @param    WP_Query $query The WP_Query instance.
	 */
	public function modify_archive_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$context_key = $this->determine_context_key( $query );
		if ( ! $context_key ) {
			return; // Not a context we manage.
		}

		$rules     = get_option( Ap_Library_Archive_Settings::OPTION_NAME );
		$defaults  = ( new Ap_Library_Archive_Settings() )->get_default_rules();
		$rule      = isset( $rules[ $context_key ] ) ? $rules[ $context_key ] : ( $defaults[ $context_key ] ?? null );

		if ( ! $rule || empty( $rule['post_types'] ) ) {
			return;
		}

		// Check if rule is enabled
		$is_enabled = isset( $rule['enabled'] ) ? (bool) $rule['enabled'] : true;
		if ( ! $is_enabled ) {
			return; // Skip modification, use default WordPress behavior
		}

		$query->set( 'post_type', $rule['post_types'] );
		$query->set( 'post_status', 'publish' );

		// Set posts per page if specified
		if ( isset( $rule['posts_per_page'] ) && $rule['posts_per_page'] !== '' ) {
			$posts_per_page = (int) $rule['posts_per_page'];
			$query->set( 'posts_per_page', $posts_per_page );
		}

		$orderby  = isset( $rule['orderby'] ) ? $rule['orderby'] : 'meta_value';
		$order    = ( isset( $rule['order'] ) && in_array( strtoupper( $rule['order'] ), [ 'ASC', 'DESC' ], true ) ) ? strtoupper( $rule['order'] ) : 'DESC';
		$meta_key = isset( $rule['meta_key'] ) ? $rule['meta_key'] : '';

		if ( 'meta_value' === $orderby && $meta_key ) {
			$query->set( 'meta_key', $meta_key );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', $order );
		} else {
			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );
		}
	}

	/**
	 * Determine context key for current query.
	 *
	 * @param WP_Query $query Query object.
	 * @return string|null Context key or null if unmanaged.
	 */
	private function determine_context_key( $query ) {
		if ( $query->is_tax( 'aplb_genre' ) ) {
			return 'tax:aplb_genre';
		}
		if ( $query->is_tax( 'aplb_taken_date' ) ) {
			return 'tax:aplb_taken_date';
		}
		if ( $query->is_tax( 'aplb_published_date' ) ) {
			return 'tax:aplb_published_date';
		}
		if ( $query->is_tax( 'aplb_keyword' ) ) {
			return 'tax:aplb_keyword';
		}
		if ( $query->is_post_type_archive( 'aplb_photo' ) ) {
			return 'post_type:aplb_photo';
		}
		if ( $query->is_author() ) {
			return 'author';
		}
		if ( $query->is_date() ) {
			return 'date';
		}
		if ( $query->is_search() ) {
			return 'search';
		}
		if ( $query->is_front_page() && $query->is_home() ) {
			return 'front-page';
		}
		return null;
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
