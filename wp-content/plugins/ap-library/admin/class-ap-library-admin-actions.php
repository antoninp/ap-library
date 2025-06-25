<?php


/**
 * The admin-specific actions of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
 * The admin-specific actions of the plugin.
 *
 * Defines the plugin name, version and actions
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library_Admin_Actions {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


    /**
	 * The actions of this admin menu.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $actions    The actions of this admin menu.
	 */
    private $actions;

    /**
     * The last admin notice message.
     *
     * @since    1.0.0
     * @access   public
     * @var      array    $last_notice    The last admin notice message.
     */
    public $last_notice;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->actions = array();
        $this->last_notice = null;

	}
    public function register_action( $key, $label, $callback ) {
        $this->actions[ $key ] = array(
            'label'    => $label,
            'callback' => $callback,
        );
    }

    public function render_buttons() {
        foreach ( $this->actions as $key => $action ) {
            ?>
            <form method="post" style="display:inline;">
                <?php wp_nonce_field( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key ); ?>
                <input type="submit" name="ap_library_run_<?php echo esc_attr( $key ); ?>" class="button button-primary" value="<?php echo esc_attr( $action['label'] ); ?>">
            </form>
            <?php
        }
    }

    public function handle_actions() {
        foreach ( $this->actions as $key => $action ) {
            if (
                isset( $_POST[ 'ap_library_run_' . $key ] ) &&
                check_admin_referer( 'ap_library_action_' . $key, 'ap_library_nonce_' . $key )
            ) {
                try {
                    $result = call_user_func( $action['callback'] );
                    if ( is_wp_error( $result ) ) {
                        throw new Exception( $result->get_error_message() );
                    }
                    // Store the notice in a property or return it, so the admin class or loader can display it.
                    $this->last_notice = [
                        'type' => 'success',
                        'message' => sprintf('%s executed successfully!', esc_html($action['label']))
                    ];
                } catch ( Exception $e ) {
                    // Store the error message in a property or return it, so the admin class or loader can display it.
                    $this->last_notice = [
                        'type' => 'error',
                        'message' => sprintf( esc_html__( '%s failed: %s', 'ap-library' ), esc_html( $action['label'] ), esc_html( $e->getMessage() ) )
                    ];
                }
            }
        }
    }

    public function run_first_action() {
        $today = date( 'Y-m-d' );

        // Get the term ID for today's pdate
        $pdate_term = term_exists( $today, 'aplb_library_pdate' );
        if ( ! ( $pdate_term && is_array( $pdate_term ) ) ) {
            echo esc_html__( 'No uploads found for today.', 'ap-library' );
            return new WP_Error('ap_library_error', 'No uploads found for today.');
        }
        $pdate_term_id = $pdate_term['term_id'];

        // Get all aplb_uploads posts published with pdate set as today
        $uploads = get_posts([
            'post_type'      => 'aplb_uploads',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'tax_query'      => [[
                'taxonomy' => 'aplb_library_pdate',
                'field'    => 'term_id',
                'terms'    => $pdate_term_id,
            ]],
        ]);
        if ( empty( $uploads ) ) {
            echo esc_html__( 'No uploads found for today.', 'ap-library' );
            return new WP_Error('ap_library_error', 'No uploads found for today.');
        }

        // Group uploads by genre
        $uploads_by_genre = [];
        foreach ( $uploads as $upload ) {
            $genres = wp_get_post_terms( $upload->ID, 'aplb_uploads_genre', [ 'fields' => 'ids' ] );
            if ( empty( $genres ) ) $genres = [ 0 ]; // Use 0 for "All"
            foreach ( $genres as $genre_id ) {
                $uploads_by_genre[ $genre_id ][] = $upload;
            }
        }

        $created = 0;
        foreach ( $uploads_by_genre as $genre_id => $genre_uploads ) {
            $image_ids = [];
            $images_json = [];
            foreach ( $genre_uploads as $upload ) {
                $thumb_id = get_post_thumbnail_id( $upload->ID );
                if ( $thumb_id ) {
                    $image_ids[] = $thumb_id;
                    $images_json[] = [
                        'alt'     => '',
                        'id'      => $thumb_id,
                        'url'     => esc_url( wp_get_attachment_url( $thumb_id ) ),
                        'caption' => ''
                    ];
                }
            }
            $image_ids = array_unique( $image_ids );
            if ( empty( $image_ids ) ) {
                continue;
            }

            // Get or create the library category term for this genre
            $library_cat_id = $this->get_or_create_library_category_id( $genre_id );

            // Check for a published aplb_library post for this genre and today
            $published_library_posts = $this->get_library_post_for_genre_today( $library_cat_id, $today );

            if ( ! empty( $published_library_posts ) ) {
                // Update the existing published post
                $library_post = $published_library_posts[0];
                $existing_content = $library_post->post_content;

                // Extract existing image IDs from the gallery shortcode in the content
                preg_match('/\[gallery ids="([^"]*)"/', $existing_content, $matches);
                $existing_ids = [];
                if ( isset( $matches[1] ) ) {
                    $existing_ids = array_map( 'intval', explode( ',', $matches[1] ) );
                }

                // Find new image IDs not already in the gallery
                $new_image_ids = array_diff( $image_ids, $existing_ids );
                if ( empty( $new_image_ids ) ) {
                    // Still update taxonomy if needed
                    if ( $library_cat_id ) {
                        wp_set_post_terms( $library_post->ID, [ $library_cat_id ], 'aplb_library_category', false );
                    }
                    if ( ! empty( $pdate_term_id ) ) {
                        wp_set_post_terms( $library_post->ID, [ $pdate_term_id ], 'aplb_library_pdate', false );
                    }
                    continue;
                }

                // Merge and rebuild gallery
                $merged_ids = array_unique( array_merge( $existing_ids, $image_ids ) );
                $merged_images_json = [];
                foreach ( $merged_ids as $id ) {
                    $merged_images_json[] = [
                        'alt'     => '',
                        'id'      => $id,
                        'url'     => esc_url( wp_get_attachment_url( $id ) ),
                        'caption' => ''
                    ];
                }
                $merged_gallery_html = $this->build_gallery_html( $merged_ids, $merged_images_json );

                // Update the aplb_library post
                wp_update_post([
                    'ID'           => $library_post->ID,
                    'post_content' => $merged_gallery_html,
                ]);
                // Update taxonomy
                if ( $library_cat_id ) {
                    wp_set_post_terms( $library_post->ID, [ $library_cat_id ], 'aplb_library_category', false );
                }
                if ( ! empty( $pdate_term_id ) ) {
                    wp_set_post_terms( $library_post->ID, [ $pdate_term_id ], 'aplb_library_pdate', false );
                }
                $created++;
            } else {
                // Create new aplb_library post for this genre and today
                $genre_term = $genre_id ? get_term( $genre_id, 'aplb_uploads_genre' ) : null;
                $genre_name = $genre_term ? $genre_term->name : __( 'All', 'ap-library' );
                $post_title = sprintf( __( '%s - %s', 'ap-library' ), $today, $genre_name );
                $gallery_html = $this->build_gallery_html( $image_ids, $images_json );

                $new_post = [
                    'post_title'    => $post_title,
                    'post_content'  => $gallery_html,
                    'post_status'   => 'publish',
                    'post_type'     => 'aplb_library',
                ];
                $post_id = wp_insert_post( $new_post );
                if ( $post_id && $library_cat_id ) {
                    wp_set_post_terms( $post_id, [ $library_cat_id ], 'aplb_library_category', false );
                }
                if ( $post_id && ! empty( $pdate_term_id ) ) {
                    wp_set_post_terms( $post_id, [ $pdate_term_id ], 'aplb_library_pdate', false );
                }
                if ( $post_id ) {
                    $created++;
                }
            }
        }

        if ( $created ) {
            echo esc_html( sprintf( __( '%d aplb_library post(s) created/updated for today\'s genres.', 'ap-library' ), $created ) );
            return true;
        } else {
            echo esc_html__( 'No aplb_library posts created or updated.', 'ap-library' );
            return new WP_Error('ap_library_error', 'No aplb_library posts created or updated.');
        }
    }

    
	public function run_second_action() {
		// ...your code...
		// Example error:
		// return new WP_Error('ap_library_error', 'Something went wrong.');
		return true;
	}


    /**
     * Get or create the library category term for a given genre ID.
     *
     * @param int $genre_id The genre ID.
     * @return int The term ID of the library category.
     */

    private function get_or_create_library_category_id( $genre_id ) {
        if ( $genre_id && $genre_id !== 0 ) {
            $genre_term = get_term( $genre_id, 'aplb_uploads_genre' );
            $genre_name = $genre_term ? $genre_term->name : __( 'All', 'ap-library' );
            $genre_slug = $genre_term ? $genre_term->slug : 'all';
        } else {
            $genre_name = __( 'All', 'ap-library' );
            $genre_slug = 'all';
        }
        $library_cat_term = term_exists( $genre_slug, 'aplb_library_category' );
        if ( $library_cat_term && is_array( $library_cat_term ) ) {
            return $library_cat_term['term_id'];
        } else {
            $new_cat = wp_insert_term( $genre_name, 'aplb_library_category', array( 'slug' => $genre_slug ) );
            return ! is_wp_error( $new_cat ) ? $new_cat['term_id'] : 0;
        }
    }

    /**
     * Build the HTML for the gallery block.
     *
     * @param array $image_ids The image IDs.
     * @param array $images_json The images JSON data.
     * @return string The gallery HTML.
     */
    private function build_gallery_html( $image_ids, $images_json ) {
        $gallery_class = (count($image_ids) === 1) ? 'single-image' : '';
        $gallery_shortcode = '[gallery ids="' . implode( ',', $image_ids ) . '" layout="tiles"]';
        return '<!-- wp:group {"className":"' . esc_attr($gallery_class) . '"} -->' .
            '<div class="wp-block-group ' . esc_attr($gallery_class) . '">' .
                '<!-- wp:meow-gallery/gallery ' . json_encode([
                    'images' => $images_json,
                    'layout' => 'tiles'
                ]) . ' -->' .
                $gallery_shortcode .
                '<!-- /wp:meow-gallery/gallery -->' .
            '</div>' .
        '<!-- /wp:group -->';
    }

    /**
     * Get the library post for a specific genre and today.
     *
     * @param int $genre_id The genre ID.
     * @param string $today The date in Y-m-d format.
     * @return array The library post(s) for the genre and date.
     */
    private function get_library_post_for_genre_today( $genre_id, $today ) {
        $args = array(
            'post_type'      => 'aplb_library',
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => 1,
            'date_query'     => array(
                array(
                    'after'     => $today . ' 00:00:00',
                    'before'    => $today . ' 23:59:59',
                    'inclusive' => true,
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'aplb_library_category',
                    'field'    => 'term_id',
                    'terms'    => $genre_id,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        return get_posts( $args );
    }

}