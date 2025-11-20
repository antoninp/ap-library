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
        $this->register_aplb_uploads_keyword_taxonomy();
 
    }
    
    /**
     * Register the published date taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_library_pdate_taxonomy() {

        $args = [
            'label'  => esc_html__( 'Published Date (Auto)', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Published Date (Auto)', 'ap-library' ),
                'name'               => esc_html__( 'Published Date (Auto)', 'ap-library' ),
                'singular_name'      => esc_html__( 'Published Date', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Published Date', 'ap-library' ),
                'new_item'           => esc_html__( 'New Published Date', 'ap-library' ),
                'view_item'          => esc_html__( 'View Published Date', 'ap-library' ),
                'not_found'          => esc_html__( 'No Published Date found', 'ap-library' ),
                'not_found_in_trash' => esc_html__( 'No Published Date found in trash', 'ap-library' ),
                'all_items'          => esc_html__( 'All Published Dates', 'ap-library' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_quick_edit'  => false,
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
            'label'  => esc_html__( 'Taken Date (Auto)', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Taken Date (Auto)', 'ap-library' ),
                'name'               => esc_html__( 'Taken Date (Auto)', 'ap-library' ),
                'singular_name'      => esc_html__( 'Taken Date', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Taken Date', 'ap-library' ),
                'new_item'           => esc_html__( 'New Taken Date', 'ap-library' ),
                'view_item'          => esc_html__( 'View Taken Date', 'ap-library' ),
                'not_found'          => esc_html__( 'No Taken Date found', 'ap-library' ),
                'not_found_in_trash' => esc_html__( 'No Taken Date found in trash', 'ap-library' ),
                'all_items'          => esc_html__( 'All Taken Dates', 'ap-library' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_quick_edit'  => false,
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
            'label'  => esc_html__( 'Photo Genre', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Photo Genre', 'ap-library' ),
                'name'               => esc_html__( 'Photo Genre', 'ap-library' ),
                'singular_name'      => esc_html__( 'Genre', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Genre', 'ap-library' ),
                'new_item'           => esc_html__( 'New Genre', 'ap-library' ),
                'view_item'          => esc_html__( 'View Genres', 'ap-library' ),
                'not_found'          => esc_html__( 'No Genre found', 'ap-library' ),
                'not_found_in_trash' => esc_html__( 'No Genre found in trash', 'ap-library' ),
                'all_items'          => esc_html__( 'All Photo Genres', 'ap-library' ),
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

    /**
     * Register the keyword taxonomy (auto from EXIF/IPTC keywords).
     *
     * @since    1.2.0
     * @access   private
     */
    private function register_aplb_uploads_keyword_taxonomy() {

        $args = [
            'label'  => esc_html__( 'Photo Keywords', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Photo Keywords', 'ap-library' ),
                'name'               => esc_html__( 'Photo Keywords', 'ap-library' ),
                'singular_name'      => esc_html__( 'Keyword', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Keyword', 'ap-library' ),
                'new_item'           => esc_html__( 'New Keyword', 'ap-library' ),
                'view_item'          => esc_html__( 'View Keyword', 'ap-library' ),
                'not_found'          => esc_html__( 'No Keyword found', 'ap-library' ),
                'not_found_in_trash' => esc_html__( 'No Keyword found in trash', 'ap-library' ),
                'all_items'          => esc_html__( 'All Photo Keywords', 'ap-library' ),
            ],
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_quick_edit'  => true,
            'show_in_rest'        => true,
            'hierarchical'        => false,
            'rewrite'             => array( 'slug' => 'uploads-keyword' ),
        ];

        register_taxonomy( 'aplb_uploads_keyword', 'aplb_uploads', $args );
    }

}