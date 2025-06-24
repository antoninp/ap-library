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
	    $today = date( 'Y-m-d' );

	    // Get the term ID for today's pdate
	    $pdate_term = term_exists( $today, 'aplb_library_pdate' );
	    if ( $pdate_term && is_array( $pdate_term ) ) {
	        $pdate_term_id = $pdate_term['term_id'];
	    } else {
	        // If no posts have this pdate, nothing to do
	        echo esc_html__( 'No uploads found for today.', 'ap-library' );
	        return new WP_Error('ap_library_error', 'No uploads found for today.');
	    }

	    // 1. Get all aplb_uploads posts published with pdate set as today
	    $args = array(
	        'post_type'      => 'aplb_uploads',
	        'post_status'    => 'publish',
	        'posts_per_page' => -1,
	        'tax_query'      => array(
	            array(
	                'taxonomy' => 'aplb_library_pdate',
	                'field'    => 'term_id',
	                'terms'    => $pdate_term_id,
	            ),
	        ),
	    );
	    $uploads = get_posts( $args );

	    if ( empty( $uploads ) ) {
	        echo esc_html__( 'No uploads found for today.', 'ap-library' );
	        return new WP_Error('ap_library_error', 'No uploads found for today.');
	    }

	    // 2. Group uploads by uploads_genre
	    $uploads_by_genre = array();
	    foreach ( $uploads as $upload ) {
	        $genres = wp_get_post_terms( $upload->ID, 'aplb_uploads_genre', array( 'fields' => 'ids' ) );
	        if ( empty( $genres ) ) {
	            $genres = array( 0 ); // Use 0 for "no genre"
	        }
	        foreach ( $genres as $genre_id ) {
	            if ( ! isset( $uploads_by_genre[ $genre_id ] ) ) {
	                $uploads_by_genre[ $genre_id ] = array();
	            }
	            $uploads_by_genre[ $genre_id ][] = $upload;
	        }
	    }

	    $created = 0;
	    foreach ( $uploads_by_genre as $genre_id => $genre_uploads ) {
	        $image_ids = array();
	        $images_json = array();

	        foreach ( $genre_uploads as $upload ) {
	            $thumb_id = get_post_thumbnail_id( $upload->ID );
	            if ( $thumb_id ) {
	                $image_ids[] = $thumb_id;
	                $images_json[] = array(
	                    'alt'     => '',
	                    'id'      => $thumb_id,
	                    'url'     => esc_url( wp_get_attachment_url( $thumb_id ) ),
	                    'caption' => ''
	                );
	            }
	        }

	        $image_ids = array_unique( $image_ids );
	        if ( empty( $image_ids ) ) {
	            continue;
	        }

			// Get the genre term object for naming and taxonomy assignment
			if ( $genre_id && $genre_id !== 0 ) {
				$genre_term = get_term( $genre_id, 'aplb_uploads_genre' );
				$genre_name = $genre_term ? $genre_term->name : __( 'All', 'ap-library' );
				$genre_slug = $genre_term ? $genre_term->slug : 'all';
			} else {
				$genre_name = __( 'All', 'ap-library' );
				$genre_slug = 'all';
			}

			// Ensure the genre exists in aplb_library_category and get its ID
			$library_cat_term = term_exists( $genre_slug, 'aplb_library_category' );
			if ( $library_cat_term && is_array( $library_cat_term ) ) {
				$library_cat_id = $library_cat_term['term_id'];
			} else {
				$new_cat = wp_insert_term( $genre_name, 'aplb_library_category', array( 'slug' => $genre_slug ) );
				$library_cat_id = ! is_wp_error( $new_cat ) ? $new_cat['term_id'] : 0;
			}

	        // Find any aplb_library post for this genre and today (any status)
	        $library_args = array(
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
	        $library_posts = get_posts( $library_args );

	        // Find any published aplb_library post for this genre and today
	        $published_library_args = $library_args;
	        $published_library_args['post_status'] = 'publish';
	        $published_library_posts = get_posts( $published_library_args );

	        // If a published post exists, update it; otherwise, create a new one
	        if ( ! empty( $published_library_posts ) ) {
	            $library_post = $published_library_posts[0];
	            $existing_content = $library_post->post_content;

	            // Extract existing image IDs from the gallery shortcode in the content
	            preg_match('/\[gallery ids="([^"]*)"/', $existing_content, $matches);
	            $existing_ids = array();
	            if ( isset( $matches[1] ) ) {
	                $existing_ids = array_map( 'intval', explode( ',', $matches[1] ) );
	            }

	            // Find new image IDs not already in the gallery
	            $new_image_ids = array_diff( $image_ids, $existing_ids );

	            if ( empty( $new_image_ids ) ) {
	                // Still update library_category and pdate terms if needed
	                if ( $library_cat_id ) {
	                    wp_set_post_terms( $library_post->ID, array( $library_cat_id ), 'aplb_library_category', false );
	                }
	                if ( ! empty( $pdate_term_id ) ) {
	                    wp_set_post_terms( $library_post->ID, array( $pdate_term_id ), 'aplb_library_pdate', false );
	                }
	                continue; // No new images to add
	            }

	            // Merge and rebuild gallery
	            $merged_ids = array_unique( array_merge( $existing_ids, $image_ids ) );
	            $merged_images_json = array();
	            foreach ( $merged_ids as $id ) {
	                $merged_images_json[] = array(
	                    'alt'     => '',
	                    'id'      => $id,
	                    'url'     => esc_url( wp_get_attachment_url( $id ) ),
	                    'caption' => ''
	                );
	            }
	            $gallery_class = (count($merged_ids) === 1) ? 'single-image' : '';
	            $merged_gallery_shortcode = '[gallery ids="' . implode( ',', $merged_ids ) . '" layout="tiles"]';
	            $merged_gallery_html =
	                '<!-- wp:group {"className":"' . esc_attr($gallery_class) . '"} -->' .
	                    '<div class="wp-block-group ' . esc_attr($gallery_class) . '">' .
	                        '<!-- wp:meow-gallery/gallery ' . json_encode([
	                            'images' => $merged_images_json,
	                            'layout' => 'tiles'
	                        ]) . ' -->' .
	                        $merged_gallery_shortcode .
	                        '<!-- /wp:meow-gallery/gallery -->' .
	                    '</div>' .
	                '<!-- /wp:group -->';

	            // Update the aplb_library post
	            wp_update_post( array(
	                'ID'           => $library_post->ID,
	                'post_content' => $merged_gallery_html,
	            ) );
	            // Update taxonomy
	            if ( $library_cat_id ) {
	                wp_set_post_terms( $library_post->ID, array( $library_cat_id ), 'aplb_library_category', false );
	            }
	            if ( ! empty( $pdate_term_id ) ) {
	                wp_set_post_terms( $library_post->ID, array( $pdate_term_id ), 'aplb_library_pdate', false );
	            }
	            $created++;
	        } else {
	            // Create new aplb_library post for this genre and today
	            $post_title = sprintf( __( '%s - %s', 'ap-library' ), $today, $genre_name );
	            $gallery_class = (count($image_ids) === 1) ? 'single-image' : '';
	            $gallery_shortcode = '[gallery ids="' . implode( ',', $image_ids ) . '" layout="tiles"]';
	            $gallery_html =
	                '<!-- wp:group {"className":"' . esc_attr($gallery_class) . '"} -->' .
	                    '<div class="wp-block-group ' . esc_attr($gallery_class) . '">' .
	                        '<!-- wp:meow-gallery/gallery ' . json_encode([
	                            'images' => $images_json,
	                            'layout' => 'tiles'
	                        ]) . ' -->' .
	                        $gallery_shortcode .
	                        '<!-- /wp:meow-gallery/gallery -->' .
	                    '</div>' .
	                '<!-- /wp:group -->';

	            $new_post = array(
	                'post_title'    => $post_title,
	                'post_content'  => $gallery_html,
	                'post_status'   => 'publish',
	                'post_type'     => 'aplb_library',
	            );
	            $post_id = wp_insert_post( $new_post );
	            if ( $post_id && $library_cat_id ) {
	                wp_set_post_terms( $post_id, array( $library_cat_id ), 'aplb_library_category', false );
	            }
	            if ( $post_id && ! empty( $pdate_term_id ) ) {
	                wp_set_post_terms( $post_id, array( $pdate_term_id ), 'aplb_library_pdate', false );
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

	/**
	 * Add a thumbnail column to aplb_uploads post list.
	 */
	public function add_aplb_uploads_thumbnail_column( $columns ) {
	    // Insert the thumbnail column after the checkbox
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

