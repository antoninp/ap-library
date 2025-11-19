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
            'label'  => esc_html__( 'Uploads', 'text-domain' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Uploads', 'ap_uploads' ),
                'name'               => esc_html__( 'Uploads', 'ap_uploads' ),
                'singular_name'      => esc_html__( 'Upload', 'ap_uploads' ),
                'add_new'            => esc_html__( 'Add Uploads', 'ap_uploads' ),
                'add_new_item'       => esc_html__( 'Add new Uploads', 'ap_uploads' ),
                'new_item'           => esc_html__( 'New Uploads', 'ap_uploads' ),
                'edit_item'          => esc_html__( 'Edit Uploads', 'ap_uploads' ),
                'view_item'          => esc_html__( 'View Uploads', 'ap_uploads' ),
                'update_item'        => esc_html__( 'Update Uploads', 'ap_uploads' ),
                'all_items'          => esc_html__( 'All Uploads', 'ap_uploads' ),
                'search_items'       => esc_html__( 'Search Uploads', 'ap_uploads' ),
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
            'taxonomies' => [
                'uploads_tdate',
                'uploads_genre',
                'library_pdate'
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
            'label'  => esc_html__( 'Library', 'text-domain' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Library', 'ap_library' ),
                'name'               => esc_html__( 'Library', 'ap_library' ),
                'singular_name'      => esc_html__( 'Library', 'ap_library' ),
                'add_new'            => esc_html__( 'Add Library', 'ap_library' ),
                'add_new_item'       => esc_html__( 'Add new Library', 'ap_library' ),
                'new_item'           => esc_html__( 'New Library', 'ap_library' ),
                'edit_item'          => esc_html__( 'Edit Library', 'ap_library' ),
                'view_item'          => esc_html__( 'View Library', 'ap_library' ),
                'update_item'        => esc_html__( 'View Library', 'ap_library' ),
                'all_items'          => esc_html__( 'All Library', 'ap_library' ),
                'search_items'       => esc_html__( 'Search Library', 'ap_library' ),
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
            'taxonomies' => [
                'library_category',
                'library_pdate'
            ],
            'rewrite' => array( 'slug' => 'library' ),
        ];


        register_post_type( 'aplb_library', $args );
    }
}