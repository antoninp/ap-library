<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// 1. Delete all custom posts
$aplb_post_types = ['aplb_uploads', 'aplb_library', 'uploads'];
foreach ( $aplb_post_types as $post_type ) {
    $posts = get_posts([
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ]);
    foreach ( $posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}

// 2. Delete all custom taxonomy terms
$aplb_taxonomies = [
    'aplb_uploads_tdate',
    'aplb_uploads_genre',
    'aplb_library_pdate'
];
foreach ( $aplb_taxonomies as $taxonomy ) {
    $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, $taxonomy );
        }
    }
}

// 3. Delete plugin options
delete_option( 'ap_library_auto_create_post_on_upload' );
delete_option( 'ap_library_enable_back_to_top' );
