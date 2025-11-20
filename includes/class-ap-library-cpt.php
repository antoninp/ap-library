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

        $this->register_aplb_uploads_post_type();
        $this->register_aplb_library_post_type();

    }

    /**
     * Register the uploads custom post types.
     *
     * @since    1.0.0
     */
    private function register_aplb_uploads_post_type() {
        
        $args = [
            'label'  => esc_html__( 'Uploads', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Uploads', 'ap-library' ),
                'name'               => esc_html__( 'Uploads', 'ap-library' ),
                'singular_name'      => esc_html__( 'Upload', 'ap-library' ),
                'add_new'            => esc_html__( 'Add Uploads', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Uploads', 'ap-library' ),
                'new_item'           => esc_html__( 'New Uploads', 'ap-library' ),
                'edit_item'          => esc_html__( 'Edit Uploads', 'ap-library' ),
                'view_item'          => esc_html__( 'View Uploads', 'ap-library' ),
                'update_item'        => esc_html__( 'Update Uploads', 'ap-library' ),
                'all_items'          => esc_html__( 'All Uploads', 'ap-library' ),
                'search_items'       => esc_html__( 'Search Uploads', 'ap-library' ),
            ],
            'public'              => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => false,
            'show_in_rest'        => true,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => 'uploads',
            'query_var'           => true,
            'can_export'          => true,
            'rewrite_no_front'    => false,
            'show_in_menu'        => true,
            'menu_position'         => 4,
            'menu_icon'           => 'dashicons-upload',
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
                'aplb_uploads_tdate',
                'aplb_uploads_genre',
                'aplb_library_pdate',
                'aplb_uploads_keyword'
            ],
            'rewrite' => array( 'slug' => 'lastest-uploads' ),
        ];

        register_post_type( 'aplb_uploads', $args );
    }

    /**
     * Register the library custom post types.
     *
     * @since    1.0.0
     */
    private function register_aplb_library_post_type() {
        
        $args = [
            'label'  => esc_html__( 'Library', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Library', 'ap-library' ),
                'name'               => esc_html__( 'Library', 'ap-library' ),
                'singular_name'      => esc_html__( 'Library', 'ap-library' ),
                'add_new'            => esc_html__( 'Add Library', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Library', 'ap-library' ),
                'new_item'           => esc_html__( 'New Library', 'ap-library' ),
                'edit_item'          => esc_html__( 'Edit Library', 'ap-library' ),
                'view_item'          => esc_html__( 'View Library', 'ap-library' ),
                'update_item'        => esc_html__( 'View Library', 'ap-library' ),
                'all_items'          => esc_html__( 'All Library', 'ap-library' ),
                'search_items'       => esc_html__( 'Search Library', 'ap-library' ),
            ],
            'public'              => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => false,
            'show_in_rest'        => true,
            'capability_type'     => 'post',
            'hierarchical'        => true,
            'has_archive'         => 'library',
            'query_var'           => true,
            'can_export'          => true,
            'rewrite_no_front'    => false,
            'show_in_menu'        => true,
            'menu_position'         => 5,
            'menu_icon'           => 'dashicons-images-alt2',
            'supports' => [
                'title',
                'editor',
                'author',
                'thumbnail'
            ],
            // Attach shared genre and published date taxonomies (prefixed)
            'taxonomies' => [
                'aplb_library_pdate',
                'aplb_uploads_genre'
            ],
            'rewrite' => array( 'slug' => 'library' ),
        ];


        register_post_type( 'aplb_library', $args );
    }
}