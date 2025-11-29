<?php

/**
 * The file that defines the custom post types for the plugin.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */

/**
 * Defines custom post types.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo
 */
class Ap_Library_Custom_Post_Types {

    /**
     * Register the custom post types.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        // Register photo post type (renamed from uploads)
        $this->register_aplb_photo_post_type();

    }

    /**
     * Register the photo custom post types.
     *
     * @since    1.0.0
     */
    private function register_aplb_photo_post_type() {
        
        $args = [
            'label'  => esc_html__( 'Photos', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Photos', 'ap-library' ),
                'name'               => esc_html__( 'Photos', 'ap-library' ),
                'singular_name'      => esc_html__( 'Photo', 'ap-library' ),
                'add_new'            => esc_html__( 'Add Photo', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add New Photo', 'ap-library' ),
                'new_item'           => esc_html__( 'New Photo', 'ap-library' ),
                'edit_item'          => esc_html__( 'Edit Photo', 'ap-library' ),
                'view_item'          => esc_html__( 'View Photo', 'ap-library' ),
                'update_item'        => esc_html__( 'Update Photo', 'ap-library' ),
                'all_items'          => esc_html__( 'All Photos', 'ap-library' ),
                'search_items'       => esc_html__( 'Search Photos', 'ap-library' ),
            ],
            'public'              => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'show_in_rest'        => true,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => 'photos',
            'query_var'           => true,
            'can_export'          => true,
            'rewrite_no_front'    => false,
            'show_in_menu'        => true,
            'menu_position'         => 4,
            'menu_icon'           => 'dashicons-format-image',
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'author',
                'excerpt',
                'custom-fields'
            ],
            // Attach correct registered taxonomies (prefixed) and new keywords taxonomy
            'taxonomies' => [
                'aplb_taken_date',
                'aplb_genre',
                'aplb_published_date',
                'aplb_keyword',
                'aplb_portfolio'
            ],
            'rewrite' => array( 'slug' => 'photos' ),
        ];

        register_post_type( 'aplb_photo', $args );
    }

    /**
     * Register the library custom post types.
     *
     * @since    1.0.0
     */
    // Removed library post type registration; legacy posts are no longer used.
}