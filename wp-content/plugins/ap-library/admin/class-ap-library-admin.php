<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */

 require_once plugin_dir_path( __FILE__ ) . 'class-ap-library-admin-actions.php';


class Ap_Library_Admin {

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
     * The actions manager of admin menu.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $actions_manager    The actions manager of admin menu.
     */
    private $actions_manager;

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

		// Initialize actions manager and register actions
		$this->actions_manager = new Ap_Library_Admin_Actions($this->version, $this->plugin_name);
		$this->actions_manager->register_action(
			'first_action',
			'Run First Action',
			array( $this, 'run_first_action' )
		);
		$this->actions_manager->register_action(
			'second_action',
			'Run Second Action',
			array( $this, 'run_second_action' )
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ap_Library_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ap_Library_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ap-library-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ap_Library_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ap_Library_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ap-library-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the admin menu for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'AP Library', 'ap-library' ), // Page title
			__( 'AP Library', 'ap-library' ), // Menu title
			'manage_options',                 // Capability
			'ap-library',                     // Menu slug
			array( $this, 'display_plugin_admin_page' ), // Callback
			'dashicons-open-folder',          // Icon
			25                                // Position
		);
	}

	/**
	 * Display the plugin admin page content.
	 */
	public function display_plugin_admin_page() {
		echo '<div class="ap-library-admin-wrap">';
		echo '<h1 class="ap-library-admin-title">' . esc_html__( 'AP Library Admin', 'ap-library' ) . '</h1>';

		echo '<div class="ap-library-admin-actions">';
		$this->actions_manager->render_buttons();
		echo '</div>';

		$enabled = get_option( 'ap_library_auto_create_post_on_upload', false );
		?>
		<form method="post" class="ap-library-checkbox-row">
		    <?php wp_nonce_field( 'ap_library_auto_create_post_on_upload_action', 'ap_library_auto_create_post_on_upload_nonce' ); ?>
		    <input type="checkbox" id="ap_library_auto_create_post_on_upload" name="ap_library_auto_create_post_on_upload" value="1" <?php checked( $enabled, true ); ?> />
		    <label for="ap_library_auto_create_post_on_upload">
		        <?php esc_html_e( 'Automatically create a post when an image is uploaded', 'ap-library' ); ?>
		    </label>
		    <input type="submit" class="ap-library-admin-save-btn" value="<?php esc_attr_e( 'Save', 'ap-library' ); ?>">
		</form>
		<?php

		$back_to_top_enabled = get_option( 'ap_library_enable_back_to_top', false );
		?>
		<form method="post" class="ap-library-checkbox-row">
		    <?php wp_nonce_field( 'ap_library_enable_back_to_top_action', 'ap_library_enable_back_to_top_nonce' ); ?>
		    <input type="checkbox" id="ap_library_enable_back_to_top" name="ap_library_enable_back_to_top" value="1" <?php checked( $back_to_top_enabled, true ); ?> />
		    <label for="ap_library_enable_back_to_top">
		        <?php esc_html_e( 'Enable "Back to Top" button on public pages', 'ap-library' ); ?>
		    </label>
		    <input type="submit" class="ap-library-admin-save-btn" value="<?php esc_attr_e( 'Save', 'ap-library' ); ?>">
		</form>
		<?php
		echo '</div>';
	}

	public function handle_admin_actions() {
		$this->actions_manager->handle_actions();
	}

	public function handle_auto_create_post_option() {
		if (
			isset( $_POST['ap_library_auto_create_post_on_upload_nonce'] ) &&
			wp_verify_nonce( $_POST['ap_library_auto_create_post_on_upload_nonce'], 'ap_library_auto_create_post_on_upload_action' )
		) {
			$enabled = isset( $_POST['ap_library_auto_create_post_on_upload'] ) ? true : false;
			update_option( 'ap_library_auto_create_post_on_upload', $enabled );
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ap-library' ) . '</p></div>';
			} );
		}
	}

	public function handle_back_to_top_option() {
		if (
			isset( $_POST['ap_library_enable_back_to_top_nonce'] ) &&
			wp_verify_nonce( $_POST['ap_library_enable_back_to_top_nonce'], 'ap_library_enable_back_to_top_action' )
		) {
			$enabled = isset( $_POST['ap_library_enable_back_to_top'] ) ? true : false;
			update_option( 'ap_library_enable_back_to_top', $enabled );
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ap-library' ) . '</p></div>';
			} );
		}
	}

	// Example action callbacks
	public function run_first_action() {

		//Search uploads for images of the day
		$target_date = '2025-06-17'; // Example date
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'date_query' => array(
				array(
					'year'  => date('Y', strtotime($target_date)),
					'month' => date('m', strtotime($target_date)),
					'day'   => date('d', strtotime($target_date)),
				),
			),
		);

		$query = new WP_Query( $args );
		$image_ids = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$image_ids[] = get_the_ID();
			}
			wp_reset_postdata();
		}

		//Create new gallery post with post of the day
		$post_title = 'Gallery from ' . $target_date;
		$post_content = '[gallery ids="' . implode( ',', $image_ids ) . '"]'; // The gallery shortcode

		$new_post = array(
			'post_title'    => $post_title,
			'post_content'  => $post_content,
			'post_status'   => 'draft',
			'post_type'     => 'aplb_library', // Or your custom post type
		);

		$post_id = wp_insert_post( $new_post );

		if ( $post_id ) {
			echo 'Post created with ID: ' . $post_id;
			return true;
		} else {
			echo 'Error creating post.';
			return new WP_Error('ap_library_error', 'Something went wrong.');
		}
	}

	public function run_second_action() {
		// ...your code...
		// Example error:
		// return new WP_Error('ap_library_error', 'Something went wrong.');
		return true;
	}

	/**
	 * Create a post on image upload if enabled in settings.
	 */
	public function maybe_create_post_on_image_upload( $image_id ) {
		if ( ! get_option( 'ap_library_auto_create_post_on_upload', false ) ) {
			return;
		}

		// Only fire for images.
		if ( ! wp_attachment_is_image( $image_id ) ) {
			return;
		}

		$attachment = get_post( $image_id );
		$tdate_term_id = null;
		$genre_term_id = null;
		$term_genre = 'all';

		$full_path = get_attached_file( $image_id );
		$filename = basename( $full_path, '.' . pathinfo( $full_path, PATHINFO_EXTENSION ) );
		$parts = explode( '-', $filename );
		if ( isset( $parts[0] ) ) {
			$term_slug = sanitize_title( $parts[0] );
			$term_date = substr($term_slug, 0, 4);

			$existing_term = term_exists( $term_date, 'aplb_uploads_tdate' );
			if ( $existing_term && is_array( $existing_term ) ) {
				$tdate_term_id = $existing_term['term_id'];
			} else {
				$new_term = wp_insert_term( $term_date, 'aplb_uploads_tdate' );
				if ( ! is_wp_error( $new_term ) ) {
					$tdate_term_id = $new_term['term_id'];
				}
			}

			$existing_term = term_exists( $term_genre, 'aplb_uploads_genre' );
			if ( $existing_term && is_array( $existing_term ) ) {
				$genre_term_id = $existing_term['term_id'];
			} else {
				$new_term = wp_insert_term( $term_genre, 'aplb_uploads_genre' );
				if ( ! is_wp_error( $new_term ) ) {
					$genre_term_id = $new_term['term_id'];
				}
			}
		}

		$meow_options = array(
			'ids'       => '"' . $image_id  . '"',
			'layout'    => 'tiles',
			'link'      => 'none',
			'imageSize' => 'full',
			'captions'  => false
		);

		$gallery_shortcode = '[gallery ids="' . $image_id . '" layout="tiles"]';
		$gallery_html = '<!-- wp:meow-gallery/gallery {
			"images": [{
				"alt":"",
				"id":'. $image_id . ',
				"url":"'. esc_url( wp_get_attachment_url( $image_id ) ) .'",
				"caption":""
				}],
			"layout":"tiles"} -->
				'. $gallery_shortcode .'
			<!-- /wp:meow-gallery/gallery -->';

		$new_post = array(
			'post_title'    => sanitize_text_field( $attachment->post_title ),
			'post_status'   => 'draft',
			'post_author'   => get_current_user_id(),
			'post_type'     => 'aplb_uploads',
			'tax_input'     => array(),
		);

		if ( ! empty( $tdate_term_id ) ) {
			$new_post['tax_input']['aplb_uploads_tdate'] = array( $tdate_term_id );
		}
		if ( ! empty( $genre_term_id ) ) {
			$new_post['tax_input']['aplb_uploads_genre'] = array( $genre_term_id );
		}

		$post_id = wp_insert_post( $new_post );
		if ( is_wp_error( $post_id ) ) {
			return;
		}

		set_post_thumbnail($post_id, $image_id);

		$attachment_args = array(
			'ID'           => $image_id,
			'post_parent'  => $post_id
		);
		wp_update_post( $attachment_args );

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $gallery_html
		) );
	}
}
