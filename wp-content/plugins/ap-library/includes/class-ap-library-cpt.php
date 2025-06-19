<?php

/**
 * The file that defines the custom post types for the plugin.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    AP_Library
 * @subpackage AP_Library/includes
 */

/**
 * Defines custom post types.
 *
 * @since      1.0.0
 * @package    AP_Library
 * @subpackage AP_Library/includes
 * @author     Antonin Puleo
 */
class AP_Library_Custom_Post_Types {

    /**
     * Register the custom post types.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        $args_uploads = array(
            'public'       => true,
            'label'        => 'Uploads',
            'description'  => 'Latest uploads in the library',
            'supports'     => array( 'title', 'editor', 'thumbnail' ),
            'hierarchical' => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'uploads' ), // Good practice for permalinks
        );

        $args_library = array(
            'public'       => true,
            'label'        => 'Library',
            'description'  => 'Library of galleries',
            'supports'     => array( 'title', 'editor', 'thumbnail' ),
            'hierarchical' => true,
            'has_archive'  => true,
            'rewrite'      => array( 'slug' => 'library' ), // Good practice for permalinks
        );

        register_post_type( 'aplb_uploads', $args_uploads );
        register_post_type( 'aplb_library', $args_library );
    }
}