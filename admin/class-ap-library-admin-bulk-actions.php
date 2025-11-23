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

    /**
     * The last admin notice message.
     *
     * @since    1.0.0
     * @access   public
     * @var      array    $last_notice    The last admin notice message.
     */
    public $last_notice = null;

    /**
     * Initialize the class and set its properties.
     * @since    1.0.0
     */
    public function __construct() {

    }

    /**
     * Register the custom bulk actions for the aplb_photo post type.
     *
     * @since    1.0.0
     * @param    array    $bulk_actions    The existing bulk actions.
     * @return   array                     The modified bulk actions.
     */
    public function register_photo_bulk_actions( $bulk_actions ) {
        $bulk_actions['publish_aplb_photo'] = __( 'Publish Photos', 'ap-library' );
        return $bulk_actions;
    }

    /**
     * Handle the custom bulk action for publishing photos.
     *
     * @since    1.0.0
     * @param    string    $redirect_to    The redirect URL.
     * @param    string    $doaction       The action being performed.
     * @param    array     $post_ids      The IDs of the selected posts.
     * @return   string                   The modified redirect URL.
     */
    public function handle_photo_bulk_action( $redirect_to, $doaction, $post_ids ) {
        if ( $doaction !== 'publish_aplb_photo' ) {
            return $redirect_to;
        }

        $published = 0;
        foreach ( $post_ids as $post_id ) {
            $post = get_post( $post_id );
            if ( $post && $post->post_type === 'aplb_photo' && $post->post_status !== 'publish' ) {
                wp_update_post( array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                ) );
                $published++;
            }
        }

        $redirect_to = add_query_arg( 'bulk_published_photos', $published, $redirect_to );
        return $redirect_to;
    }

    /**
     * Maybe set an admin notice after a bulk action is performed.
     *
     * @since    1.0.0
     */
    public function maybe_set_bulk_action_notice() {
        if ( ! empty( $_REQUEST['bulk_published_photos'] ) ) {
            $count = intval( $_REQUEST['bulk_published_photos'] );
            $this->last_notice = [
                'type' => 'success',
                'message' => sprintf(
                    esc_html__( 'Published %d photos.', 'ap-library' ),
                    $count
                ),
            ];
        }
    }

    /**
     * Get the last admin notice message.
     *
     * @since    1.0.0
     * @return   array|null    The last admin notice message or null if none.
     */
    public function get_last_notice() {
        return $this->last_notice;
    }
}