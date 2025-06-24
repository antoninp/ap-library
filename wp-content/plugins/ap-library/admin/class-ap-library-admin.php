<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
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
			array( $this->actions_manager, 'run_first_action' )
		);
		$this->actions_manager->register_action(
			'second_action',
			'Run Second Action',
			array( $this->actions_manager, 'run_second_action' )
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
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
		$term_genre = 'All';

		$full_path = get_attached_file( $image_id );
		$filename = basename( $full_path, '.' . pathinfo( $full_path, PATHINFO_EXTENSION ) );
		$parts = explode( '-', $filename );
		if ( isset( $parts[0] ) ) {
			$term_slug = sanitize_title( $parts[0] );
			$term_year  = substr($term_slug, 0, 4);
			$term_month = substr($term_slug, 4, 2);
			$term_day   = substr($term_slug, 6, 2);

			// 1. Year term (parent: 0)
			$year_term = term_exists( $term_year, 'aplb_uploads_tdate' );
			if ( $year_term && is_array( $year_term ) ) {
				$year_term_id = $year_term['term_id'];
			} else {
				$new_year = wp_insert_term( $term_year, 'aplb_uploads_tdate' );
				$year_term_id = ! is_wp_error( $new_year ) ? $new_year['term_id'] : 0;
			}

			// 2. Month term (parent: year)
			$month_slug = $term_year . '-' . $term_month;
			$month_term = term_exists( $month_slug, 'aplb_uploads_tdate' );
			if ( $month_term && is_array( $month_term ) ) {
				$month_term_id = $month_term['term_id'];
			} else {
				$new_month = wp_insert_term( $month_slug, 'aplb_uploads_tdate', array(
					'parent' => $year_term_id,
					'description' => $term_year . '-' . $term_month
				) );
				$month_term_id = ! is_wp_error( $new_month ) ? $new_month['term_id'] : 0;
			}

			// 3. Day term (parent: month)
			$day_slug = $term_year . '-' . $term_month . '-' . $term_day;
			$day_term = term_exists( $day_slug, 'aplb_uploads_tdate' );
			if ( $day_term && is_array( $day_term ) ) {
				$day_term_id = $day_term['term_id'];
			} else {
				$new_day = wp_insert_term( $day_slug, 'aplb_uploads_tdate', array(
					'parent' => $month_term_id,
					'description' => $term_year . '-' . $term_month . '-' . $term_day
				) );
				$day_term_id = ! is_wp_error( $new_day ) ? $new_day['term_id'] : 0;
			}
		}

		// 4. Genre term (default to 'all' if not specified)
		$existing_term = term_exists( $term_genre, 'aplb_uploads_genre' );
		if ( $existing_term && is_array( $existing_term ) ) {
			$genre_term_id = $existing_term['term_id'];
		} else {
			$new_term = wp_insert_term( $term_genre, 'aplb_uploads_genre' );
			if ( ! is_wp_error( $new_term ) ) {
				$genre_term_id = $new_term['term_id'];
			}
		}

        // 5. Create or get the pdate term based on the upload date of the image
        $upload_date = date( 'Y-m-d', strtotime( $attachment->post_date ) );
        $pdate_term = term_exists( $upload_date, 'aplb_library_pdate' );
        if ( $pdate_term && is_array( $pdate_term ) ) {
            $pdate_term_id = $pdate_term['term_id'];
        } else {
            $new_pdate = wp_insert_term( $upload_date, 'aplb_library_pdate' );
            $pdate_term_id = ! is_wp_error( $new_pdate ) ? $new_pdate['term_id'] : 0;
        }

		$tax_input = array();

        $aplb_uploads_tdate_terms = array();
        if ( ! empty( $year_term_id ) ) {
            $aplb_uploads_tdate_terms[] = $year_term_id;
        }
        if ( ! empty( $month_term_id ) ) {
            $aplb_uploads_tdate_terms[] = $month_term_id;
        }
        if ( ! empty( $day_term_id ) ) {
            $aplb_uploads_tdate_terms[] = $day_term_id;
        }
        if ( ! empty( $aplb_uploads_tdate_terms ) ) {
            $tax_input['aplb_uploads_tdate'] = $aplb_uploads_tdate_terms;
        }

        if ( ! empty( $genre_term_id ) ) {
            $tax_input['aplb_uploads_genre'] = array( $genre_term_id );
        }

		if ( ! empty( $pdate_term_id ) ) {
		    $tax_input['aplb_library_pdate'] = array( $pdate_term_id );
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
			'tax_input'     => $tax_input,
		);

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
	 * Render the thumbnail for aplb_uploads post list.
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

	// Add custom bulk action to the dropdown
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

