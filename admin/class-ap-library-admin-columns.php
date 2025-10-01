<?php

/**
 * Admin Columns functionality for the plugin
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

class Ap_Library_Admin_Columns {

    /**
     * Add a thumbnail column to the aplb_uploads post type list table.
     *
     * @since    1.0.0
     * @param    array    $columns    The existing columns.
     * @return   array                The modified columns.
     */
    public function add_aplb_uploads_thumbnail_column( $columns ) {
        $new = array();
        foreach ( $columns as $key => $value ) {
            $new[ $key ] = $value;
            if ( $key === 'cb' ) {
                $new['thumbnail'] = __( 'Thumbnail', 'ap-library' );
            }
        }
        return $new;
    }

    /**
     * Render the thumbnail column content.
     *
     * @since    1.0.0
     * @param    string    $column    The name of the column being rendered.
     * @param    int       $post_id   The ID of the current post.
     */
    public function render_aplb_uploads_thumbnail_column( $column, $post_id ) {
        if ( $column === 'thumbnail' ) {
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
            } else {
                echo '&mdash;';
            }
        }
    }
}