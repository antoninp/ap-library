<?php

/**
 * The file that defines the taxonomies for the plugin.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */


/**
 * Defines custom taxonomies.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo
 */
class Ap_Library_Taxonomy {

    /**
     * Register the taxonomies.
     *
     * @since    1.0.0
     * @access   public
     */
    public function register_taxonomies() {

        $this->register_aplb_uploads_taxonomies();
        $this->register_aplb_library_taxonomies();

    }

    /**
     * Register the library taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_library_taxonomies() {
        
        $args = [
            'label'  => esc_html__( 'Library Categories', 'text-domain' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Library Categories', 'ap_library' ),
                'name'               => esc_html__( 'Library Categories', 'ap_library' ),
                'singular_name'      => esc_html__( 'Library Category', 'ap_library' ),
                'add_new_item'       => esc_html__( 'Add new Library Category', 'ap_library' ),
                'new_item'           => esc_html__( 'New Library Category', 'ap_library' ),
                'view_item'          => esc_html__( 'View Library Category', 'ap_library' ),
                'not_found'          => esc_html__( 'No Library Category found', 'ap_library' ),
                'not_found_in_trash' => esc_html__( 'No Library Category found in trash', 'ap_library' ),
                'all_items'          => esc_html__( 'All Library Categories', 'ap_library' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_rest'        => true,
            'hierarchical'        => true,
            'rewrite'             => array( 'slug' => 'library-category' ),
        ];

        register_taxonomy( 'aplb_library_category', 'aplb_library', $args );
    }

        /**
     * Register the uploads taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_uploads_taxonomies() {

        $args = [
            'label'  => esc_html__( 'Uploads', 'text-domain' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Uploads Taken Date', 'ap_uploads' ),
                'name'               => esc_html__( 'Uploads Taken Date', 'ap_uploads' ),
                'singular_name'      => esc_html__( 'Uploads Taken Date', 'ap_uploads' ),
                'add_new_item'       => esc_html__( 'Add new Uploads Taken Date', 'ap_uploads' ),
                'new_item'           => esc_html__( 'New Uploads Taken Date', 'ap_uploads' ),
                'view_item'          => esc_html__( 'View Uploads Taken Date', 'ap_uploads' ),
                'not_found'          => esc_html__( 'No Uploads Taken Date found', 'ap_uploads' ),
                'not_found_in_trash' => esc_html__( 'No Uploads Taken Date found in trash', 'ap_uploads' ),
                'all_items'          => esc_html__( 'All Uploads Taken Date', 'ap_uploads' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_rest'        => true,
            'hierarchical'        => true,
            'rewrite'             => array( 'slug' => 'uploads-tdate' ),
        ];

        register_taxonomy( 'aplb_uploads_tdate', 'aplb_uploads', $args );
        
        $args = [
            'label'  => esc_html__( 'Uploads', 'text-domain' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Photo Genres', 'ap_uploads' ),
                'name'               => esc_html__( 'Photo Genres', 'ap_uploads' ),
                'singular_name'      => esc_html__( 'Genre', 'ap_uploads' ),
                'add_new_item'       => esc_html__( 'Add new Genres', 'ap_uploads' ),
                'new_item'           => esc_html__( 'New Genre', 'ap_uploads' ),
                'view_item'          => esc_html__( 'View Genres', 'ap_uploads' ),
                'not_found'          => esc_html__( 'No Genre found', 'ap_uploads' ),
                'not_found_in_trash' => esc_html__( 'No Genres found in trash', 'ap_uploads' ),
                'all_items'          => esc_html__( 'All Photo Genres', 'ap_uploads' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_rest'        => true,
            'hierarchical'        => true,
            'rewrite'             => array( 'slug' => 'uploads-genre' ),
        ];

        register_taxonomy( 'aplb_uploads_genre', 'aplb_uploads', $args );

    }
}