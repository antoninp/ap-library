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

        $this->register_aplb_uploads_genre_taxonomy();
        $this->register_aplb_uploads_tdate_taxonomy();
        $this->register_aplb_library_pdate_taxonomy();
 
    }
    
    /**
     * Register the published date taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_library_pdate_taxonomy() {

        $args = [
            'label'  => esc_html__( 'Published Date', 'ap_library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Published Date', 'ap_library' ),
                'name'               => esc_html__( 'Published Date', 'ap_library' ),
                'singular_name'      => esc_html__( 'Published Date', 'ap_library' ),
                'add_new_item'       => esc_html__( 'Add new Published Date', 'ap_library' ),
                'new_item'           => esc_html__( 'New Published Date', 'ap_library' ),
                'view_item'          => esc_html__( 'View Published Date', 'ap_library' ),
                'not_found'          => esc_html__( 'No Published Date found', 'ap_library' ),
                'not_found_in_trash' => esc_html__( 'No Published Date found in trash', 'ap_library' ),
                'all_items'          => esc_html__( 'All Published Dates', 'ap_library' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_rest'        => true,
            'hierarchical'        => true,
            'rewrite'             => array( 'slug' => 'library-pdate' ),
        ];

        register_taxonomy( 'aplb_library_pdate', array( 'aplb_library' , 'aplb_uploads' ), $args );

    }

    /**
     * Register the taken date taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_uploads_tdate_taxonomy() {

        $args = [
            'label'  => esc_html__( 'Taken Date', 'ap_uploads' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Taken Date', 'ap_uploads' ),
                'name'               => esc_html__( 'Taken Date', 'ap_uploads' ),
                'singular_name'      => esc_html__( 'Taken Date', 'ap_uploads' ),
                'add_new_item'       => esc_html__( 'Add new Taken Date', 'ap_uploads' ),
                'new_item'           => esc_html__( 'New Taken Date', 'ap_uploads' ),
                'view_item'          => esc_html__( 'View Taken Date', 'ap_uploads' ),
                'not_found'          => esc_html__( 'No Taken Date found', 'ap_uploads' ),
                'not_found_in_trash' => esc_html__( 'No Taken Date found in trash', 'ap_uploads' ),
                'all_items'          => esc_html__( 'All Taken Dates', 'ap_uploads' ),
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
        
    }

    /**
     * Register the genre taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_uploads_genre_taxonomy() {
        
        $args = [
            'label'  => esc_html__( 'Photo Genre', 'ap_uploads' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Photo Genre', 'ap_uploads' ),
                'name'               => esc_html__( 'Photo Genre', 'ap_uploads' ),
                'singular_name'      => esc_html__( 'Genre', 'ap_uploads' ),
                'add_new_item'       => esc_html__( 'Add new Genre', 'ap_uploads' ),
                'new_item'           => esc_html__( 'New Genre', 'ap_uploads' ),
                'view_item'          => esc_html__( 'View Genres', 'ap_uploads' ),
                'not_found'          => esc_html__( 'No Genre found', 'ap_uploads' ),
                'not_found_in_trash' => esc_html__( 'No Genre found in trash', 'ap_uploads' ),
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

        register_taxonomy( 'aplb_uploads_genre', [ 'aplb_uploads', 'aplb_library' ], $args );

    }

}