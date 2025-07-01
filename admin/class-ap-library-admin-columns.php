<?php

class Ap_Library_Admin_Columns {

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