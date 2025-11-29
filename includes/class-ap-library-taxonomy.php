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

        $this->register_aplb_genre_taxonomy();
        $this->register_aplb_taken_date_taxonomy();
        $this->register_aplb_published_date_taxonomy();
        $this->register_aplb_keyword_taxonomy();
        $this->register_aplb_portfolio_taxonomy();
 
    }
    
    /**
     * Register the published date taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_published_date_taxonomy() {

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
            'rewrite'             => array( 'slug' => 'photo-published' ),
        ];

        register_taxonomy( 'aplb_published_date', array( 'aplb_photo' ), $args );

    }

    /**
     * Register the taken date taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_taken_date_taxonomy() {

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
            'rewrite'             => array( 'slug' => 'photo-taken' ),
        ];
        register_taxonomy( 'aplb_taken_date', 'aplb_photo', $args );
        
    }

    /**
     * Register the genre taxonomy.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_aplb_genre_taxonomy() {
        
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
            'rewrite'             => array( 'slug' => 'photo-genre' ),
        ];
        register_taxonomy( 'aplb_genre', [ 'aplb_photo' ], $args );

    }

    /**
     * Register the keyword taxonomy (auto from EXIF/IPTC keywords).
     *
     * @since    1.2.0
     * @access   private
     */
    private function register_aplb_keyword_taxonomy() {

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
            'rewrite'             => array( 'slug' => 'photo-keyword' ),
        ];
        register_taxonomy( 'aplb_keyword', 'aplb_photo', $args );
    }

    /**
     * Register the portfolio taxonomy (manual curation).
     *
     * @since    1.4.0
     * @access   private
     */
    private function register_aplb_portfolio_taxonomy() {

        $args = [
            'label'  => esc_html__( 'Portfolios', 'ap-library' ),
            'labels' => [
                'menu_name'          => esc_html__( 'Portfolios', 'ap-library' ),
                'name'               => esc_html__( 'Portfolios', 'ap-library' ),
                'singular_name'      => esc_html__( 'Portfolio', 'ap-library' ),
                'add_new_item'       => esc_html__( 'Add new Portfolio', 'ap-library' ),
                'new_item'           => esc_html__( 'New Portfolio', 'ap-library' ),
                'edit_item'          => esc_html__( 'Edit Portfolio', 'ap-library' ),
                'update_item'        => esc_html__( 'Update Portfolio', 'ap-library' ),
                'view_item'          => esc_html__( 'View Portfolio', 'ap-library' ),
                'parent_item'        => esc_html__( 'Parent Portfolio', 'ap-library' ),
                'parent_item_colon'  => esc_html__( 'Parent Portfolio:', 'ap-library' ),
                'not_found'          => esc_html__( 'No Portfolio found', 'ap-library' ),
                'not_found_in_trash' => esc_html__( 'No Portfolio found in trash', 'ap-library' ),
                'all_items'          => esc_html__( 'All Portfolios', 'ap-library' ),
            ],
            'description'         => esc_html__( 'Curated collections of your best photographs. Photos can belong to multiple portfolios.', 'ap-library' ),
            'public'              => true,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_admin_column'   => true,
            'show_in_quick_edit'  => true,
            'show_in_rest'        => true,
            'hierarchical'        => true,
            'rewrite'             => array( 'slug' => 'portfolio' ),
        ];
        register_taxonomy( 'aplb_portfolio', 'aplb_photo', $args );
    }

}