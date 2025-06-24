<?php
/**
 * The file that defines the bulk actions for the admin area.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */
class Ap_Library_Admin_Bulk_Actions {

    public function register_uploads_bulk_actions( $bulk_actions ) {
        $bulk_actions['publish_aplb_uploads'] = __( 'Publish Uploads', 'ap-library' );
        return $bulk_actions;
    }

    public function handle_uploads_bulk_action( $redirect_to, $doaction, $post_ids ) {
        if ( $doaction !== 'publish_aplb_uploads' ) {
            return $redirect_to;
        }

        $published = 0;
        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( $post && $post->post_type === 'aplb_uploads' && $post->post_status !== 'publish' ) {
                wp_update_post( array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                ) );
                $published++;
            }
        }

        $redirect_to = add_query_arg( 'bulk_published_uploads', $published, $redirect_to );
        return $redirect_to;
    }

    public function bulk_action_admin_notice() {
        if ( ! empty( $_REQUEST['bulk_published_uploads'] ) ) {
            $count = intval( $_REQUEST['bulk_published_uploads'] );
            printf(
                '<div id="message" class="updated notice notice-success is-dismissible"><p>' .
                esc_html__( 'Published %d uploads.', 'ap-library' ) .
                '</p></div>',
                $count
            );
        }
    }
}