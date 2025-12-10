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
     * The columns manager of admin menu.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $columns_manager    The columns manager of admin menu.
     */
    private $columns_manager;

	/**
	 * The bulk actions manager of admin menu.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $bulk_actions_manager    The bulk actions manager of admin menu.
	 */
	private $bulk_actions_manager;

	/**
	 * The last notice to display.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $last_notice    The last notice to display.
	 */
	private $last_notice;
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
		$this->last_notice = null;

		$this->load_dependencies();

		// Initialize actions manager and register actions
		$this->actions_manager = new Ap_Library_Admin_Actions($this->plugin_name, $this->version);
        $this->columns_manager = new Ap_Library_Admin_Columns();
		$this->bulk_actions_manager = new Ap_Library_Admin_Bulk_Actions();
	}

	/**
	 * Load the required dependencies for the admin area of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function load_dependencies() {

		require_once plugin_dir_path( __FILE__ ) . 'class-ap-library-admin-actions.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-ap-library-admin-bulk-actions.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-ap-library-admin-columns.php';

	}

	// Add public getter methods for loader access
    public function get_actions_manager() {
        return (object) $this->actions_manager;
    }
    public function get_columns_manager() {
        return (object) $this->columns_manager;
    }
    public function get_bulk_actions_manager() {
        return (object) $this->bulk_actions_manager;
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// Only load on our post type list/edit screens and plugin subpages.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) { return; }
		$allowed_ids = [
			'edit-aplb_photo',        // list table
			'aplb_photo',             // single edit/add screen
			'aplb_photo_page_aplb-overview',
			'aplb_photo_page_aplb-backfill',
			'aplb_photo_page_aplb-archive-rules',
		];
		if ( in_array( $screen->id, $allowed_ids, true ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ap-library-admin.css', [], $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) { return; }
		// Only needed for Quick Edit thumbnail injection on photo list screen.
		if ( $screen->id === 'edit-aplb_photo' ) {
			// Existing admin JS
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url(__FILE__) . 'js/ap-library-admin.js',
				['jquery'],
				$this->version,
				false
			);
			// Bulk assignment script (unified for genres and portfolios)
			wp_enqueue_script(
				'ap-library-bulk-assign',
				plugin_dir_url(__FILE__) . 'js/ap-library-bulk-assign.js',
				['jquery','wp-api-fetch','underscore'],
				$this->version,
				true
			);
			
			// Bulk date editor script
			wp_enqueue_script(
				'ap-library-bulk-date',
				plugin_dir_url(__FILE__) . 'js/ap-library-bulk-date.js',
				['jquery', 'wp-api-fetch'],
				$this->version,
				true
			);
			wp_localize_script(
				'ap-library-bulk-date',
				'APLB_BulkDate',
				[
					'confirmMsg'     => esc_html__( 'Update %s for %d photos? Components to update: %s', 'ap-library' ),
					'successMsg'     => esc_html__( 'Dates updated successfully.', 'ap-library' ),
					'errorMsg'       => esc_html__( 'Failed to update dates.', 'ap-library' ),
					'postDateLabel'  => esc_html__( 'Post Date', 'ap-library' ),
					'publishedDateLabel' => esc_html__( 'Published Date', 'ap-library' ),
					'takenDateLabel' => esc_html__( 'Taken Date', 'ap-library' ),
				]
			);
			
			wp_localize_script(
				'ap-library-bulk-assign',
				'APLB_BulkGenres',
				[
					'nonce'          => wp_create_nonce( 'wp_rest' ),
					'restUrl'        => esc_url_raw( rest_url( 'ap-library/v1/assign-genres' ) ),
					'taxonomy'       => 'aplb_genre',
					'applyLabel'     => esc_html__( 'Apply Genres to Selected', 'ap-library' ),
					'replaceLabel'   => esc_html__( 'Replace existing genres', 'ap-library' ),
					'successMessage' => esc_html__( 'Genres updated successfully.', 'ap-library' ),
					'errorMessage'   => esc_html__( 'Failed assigning genres.', 'ap-library' ),
				]
			);
			// Portfolio bulk assignment uses the same script
			wp_localize_script(
				'ap-library-bulk-assign',
				'APLB_BulkPortfolios',
				[
					'nonce'          => wp_create_nonce( 'wp_rest' ),
					'restUrl'        => esc_url_raw( rest_url( 'ap-library/v1/assign-portfolios' ) ),
					'taxonomy'       => 'aplb_portfolio',
					'applyLabel'     => esc_html__( 'Apply Portfolios to Selected', 'ap-library' ),
					'replaceLabel'   => esc_html__( 'Replace existing portfolios', 'ap-library' ),
					'successMessage' => esc_html__( 'Portfolios updated successfully.', 'ap-library' ),
					'errorMessage'   => esc_html__( 'Failed assigning portfolios.', 'ap-library' ),
				]
			);
			// Location bulk assignment uses the same script
			wp_localize_script(
				'ap-library-bulk-assign',
				'APLB_BulkLocations',
				[
					'nonce'          => wp_create_nonce( 'wp_rest' ),
					'restUrl'        => esc_url_raw( rest_url( 'ap-library/v1/assign-locations' ) ),
					'taxonomy'       => 'aplb_location',
					'applyLabel'     => esc_html__( 'Apply Locations to Selected', 'ap-library' ),
					'replaceLabel'   => esc_html__( 'Replace existing locations', 'ap-library' ),
					'successMessage' => esc_html__( 'Locations updated successfully.', 'ap-library' ),
					'errorMessage'   => esc_html__( 'Failed assigning locations.', 'ap-library' ),
				]
			);
		}
	}

	/**
	 * Register the admin menu for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Overview hub for actions, settings, and status.
		add_submenu_page(
			'edit.php?post_type=aplb_photo',
			__( 'Library Overview', 'ap-library' ),
			__( 'Library Overview', 'ap-library' ),
			'manage_options',
			'aplb-overview',
			[ $this, 'display_plugin_admin_page' ]
		);
	}

	/**
	 * Display the plugin admin page content.
	 */
	public function display_plugin_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ap-library' ) );
		}
		$status = $this->get_status_snapshot();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Photo Library Overview', 'ap-library' ); ?></h1>
			<p><?php esc_html_e( 'This overview provides quick actions, status, and general settings for your photo library.', 'ap-library' ); ?></p>

			<h2><?php esc_html_e( 'Quick Actions', 'ap-library' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Run maintenance or creation tasks. These operations are safe and won’t modify existing posts unless noted.', 'ap-library' ); ?></p>
			<?php $this->actions_manager->render_buttons(); ?>

			<h2><?php esc_html_e( 'Library Status', 'ap-library' ); ?></h2>
			<table class="widefat striped" style="max-width:680px;">
				<tbody>
					<tr><td><?php esc_html_e( 'Total Photos (published)', 'ap-library' ); ?></td><td><strong><?php echo esc_html( $status['total'] ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'With Taken Date', 'ap-library' ); ?></td><td><strong><?php echo esc_html( $status['with_taken'] ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'With Published Date Meta', 'ap-library' ); ?></td><td><strong><?php echo esc_html( $status['with_published'] ); ?></strong></td></tr>
					<tr><td><?php esc_html_e( 'With Keywords', 'ap-library' ); ?></td><td><strong><?php echo esc_html( $status['with_keywords'] ); ?></strong></td></tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'General Settings', 'ap-library' ); ?></h2>
			<div style="max-width:680px;">
				<?php $this->render_overview_settings_form(); ?>
			</div>

			<h2><?php esc_html_e( 'Related Tools', 'ap-library' ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aplb_photo&page=aplb-backfill' ) ); ?>"><?php esc_html_e( 'Backfill Tools', 'ap-library' ); ?></a> — <?php esc_html_e( 'Regenerate metadata and taxonomy terms from existing photos', 'ap-library' ); ?></li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aplb_photo&page=aplb-archive-rules' ) ); ?>"><?php esc_html_e( 'Archive Rules', 'ap-library' ); ?></a> — <?php esc_html_e( 'Configure ordering and post types for archive pages', 'ap-library' ); ?></li>
				<li><a href="https://wordpress.org/support/plugin/ap-library" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation & Support', 'ap-library' ); ?></a></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render auto-create setting form and photo post creation filters.
	 *
	 * Displays settings for auto-creating posts on upload, back-to-top button,
	 * and photo filtering options (keywords, dimensions, file size).
	 *
	 * @since    1.0.0
	 * @modified 1.3.0 Added photo post creation filter settings.
	 * @modified 1.3.1 Added global date format setting (ap_library_date_format) & portfolio bulk assignment localization.
	 */
	private function render_overview_settings_form() {
		$auto_create = get_option( 'ap_library_auto_create_post_on_upload', false );
		$back_to_top = get_option( 'ap_library_enable_back_to_top', false );
		$date_format = get_option( 'ap_library_date_format', 'M j, Y' );
		$exclude_keywords = get_option( 'ap_library_exclude_keywords', 'logo,banner,icon,avatar,profile,thumbnail,thumb,background,header,footer,placeholder,default,button,badge,sprite,ui,favicon,symbol,graphic,decoration' );
		$min_width = get_option( 'ap_library_min_photo_width', 400 );
		$min_height = get_option( 'ap_library_min_photo_height', 400 );
		$min_filesize = get_option( 'ap_library_min_photo_filesize', 50 );
		?>
		<form method="post">
			<?php wp_nonce_field( 'ap_library_overview_settings_action', 'ap_library_overview_settings_nonce' ); ?>
			<p>
				<label for="ap_library_auto_create_post_on_upload">
					<input type="checkbox" id="ap_library_auto_create_post_on_upload" name="ap_library_auto_create_post_on_upload" value="1" <?php checked( $auto_create, true ); ?> />
					<?php esc_html_e( 'Automatically create a photo post when an image is uploaded', 'ap-library' ); ?>
				</label>
			</p>
			<p>
				<label for="ap_library_enable_back_to_top">
					<input type="checkbox" id="ap_library_enable_back_to_top" name="ap_library_enable_back_to_top" value="1" <?php checked( $back_to_top, true ); ?> />
					<?php esc_html_e( 'Enable "Back to Top" button on public photo pages', 'ap-library' ); ?>
				</label>
			</p>
			
			<p>
				<label for="ap_library_date_format">
					<?php esc_html_e( 'Date format:', 'ap-library' ); ?><br>
					<select id="ap_library_date_format" name="ap_library_date_format">
						<option value="M j, Y" <?php selected( $date_format, 'M j, Y' ); ?>><?php echo esc_html( date_i18n( 'M j, Y', current_time( 'timestamp' ) ) ); ?> (<?php esc_html_e( 'Short', 'ap-library' ); ?>)</option>
						<option value="F j, Y" <?php selected( $date_format, 'F j, Y' ); ?>><?php echo esc_html( date_i18n( 'F j, Y', current_time( 'timestamp' ) ) ); ?> (<?php esc_html_e( 'Full', 'ap-library' ); ?>)</option>
						<option value="Y-m-d" <?php selected( $date_format, 'Y-m-d' ); ?>><?php echo esc_html( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ); ?> (<?php esc_html_e( 'ISO', 'ap-library' ); ?>)</option>
						<option value="m/d/Y" <?php selected( $date_format, 'm/d/Y' ); ?>><?php echo esc_html( date_i18n( 'm/d/Y', current_time( 'timestamp' ) ) ); ?> (<?php esc_html_e( 'US', 'ap-library' ); ?>)</option>
						<option value="d/m/Y" <?php selected( $date_format, 'd/m/Y' ); ?>><?php echo esc_html( date_i18n( 'd/m/Y', current_time( 'timestamp' ) ) ); ?> (<?php esc_html_e( 'EU', 'ap-library' ); ?>)</option>
					</select>
				</label>
				<br><small class="description"><?php esc_html_e( 'Format for displaying dates in photo list columns and taxonomy terms. Applies to newly created dates.', 'ap-library' ); ?></small>
			</p>
			
			<h3><?php esc_html_e( 'Photo Post Creation Filters', 'ap-library' ); ?></h3>
			<p class="description"><?php esc_html_e( 'These filters exclude non-photograph images (logos, icons, banners) when creating photo posts.', 'ap-library' ); ?></p>
			
			<p>
				<label for="ap_library_exclude_keywords">
					<?php esc_html_e( 'Exclude filenames containing (comma-separated):', 'ap-library' ); ?><br>
					<input type="text" id="ap_library_exclude_keywords" name="ap_library_exclude_keywords" value="<?php echo esc_attr( $exclude_keywords ); ?>" class="regular-text" />
				</label>
			</p>
			
			<p>
				<label for="ap_library_min_photo_width">
					<?php esc_html_e( 'Minimum width (pixels):', 'ap-library' ); ?>
					<input type="number" id="ap_library_min_photo_width" name="ap_library_min_photo_width" value="<?php echo esc_attr( $min_width ); ?>" min="0" step="1" style="width:80px;" />
				</label>
				<label for="ap_library_min_photo_height" style="margin-left:20px;">
					<?php esc_html_e( 'Minimum height (pixels):', 'ap-library' ); ?>
					<input type="number" id="ap_library_min_photo_height" name="ap_library_min_photo_height" value="<?php echo esc_attr( $min_height ); ?>" min="0" step="1" style="width:80px;" />
				</label>
				<br><small class="description"><?php esc_html_e( 'Images smaller than these dimensions will be excluded (set to 0 to disable).', 'ap-library' ); ?></small>
			</p>
			
			<p>
				<label for="ap_library_min_photo_filesize">
					<?php esc_html_e( 'Minimum file size (KB):', 'ap-library' ); ?>
					<input type="number" id="ap_library_min_photo_filesize" name="ap_library_min_photo_filesize" value="<?php echo esc_attr( $min_filesize ); ?>" min="0" step="1" style="width:80px;" />
				</label>
				<br><small class="description"><?php esc_html_e( 'Images smaller than this file size will be excluded (set to 0 to disable).', 'ap-library' ); ?></small>
			</p>
			
			<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'ap-library' ); ?>" /></p>
		</form>
		<?php
	}

	/**
	 * Unified settings handler for overview page.
	 *
	 * Processes and saves all overview page settings including auto-create,
	 * back-to-top, and photo post creation filters.
	 *
	 * @since    1.0.0
	 * @modified 1.3.0 Added handling for photo post creation filter settings.
	 * @modified 1.3.1 Added saving of global date format setting and validation.
	 */
	public function handle_overview_settings() {
		if (
			isset( $_POST['ap_library_overview_settings_nonce'] ) &&
			wp_verify_nonce( $_POST['ap_library_overview_settings_nonce'], 'ap_library_overview_settings_action' ) &&
			current_user_can( 'manage_options' )
		) {
			$auto_create = isset( $_POST['ap_library_auto_create_post_on_upload'] );
			$back_to_top = isset( $_POST['ap_library_enable_back_to_top'] );
			update_option( 'ap_library_auto_create_post_on_upload', $auto_create );
			update_option( 'ap_library_enable_back_to_top', $back_to_top );
			
			// Save date format
			if ( isset( $_POST['ap_library_date_format'] ) ) {
				$allowed_formats = [ 'M j, Y', 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ];
				$format = sanitize_text_field( $_POST['ap_library_date_format'] );
				if ( in_array( $format, $allowed_formats, true ) ) {
					update_option( 'ap_library_date_format', $format );
				}
			}
			
			// Save filter settings
			if ( isset( $_POST['ap_library_exclude_keywords'] ) ) {
				update_option( 'ap_library_exclude_keywords', sanitize_text_field( $_POST['ap_library_exclude_keywords'] ) );
			}
			if ( isset( $_POST['ap_library_min_photo_width'] ) ) {
				update_option( 'ap_library_min_photo_width', absint( $_POST['ap_library_min_photo_width'] ) );
			}
			if ( isset( $_POST['ap_library_min_photo_height'] ) ) {
				update_option( 'ap_library_min_photo_height', absint( $_POST['ap_library_min_photo_height'] ) );
			}
			if ( isset( $_POST['ap_library_min_photo_filesize'] ) ) {
				update_option( 'ap_library_min_photo_filesize', absint( $_POST['ap_library_min_photo_filesize'] ) );
			}
			
			$this->last_notice = [ 'type' => 'success', 'message' => __( 'Settings saved.', 'ap-library' ) ];
		}
	}

	/**
	 * Build a status snapshot with simple counts.
	 */
	private function get_status_snapshot() {
		global $wpdb;
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='aplb_photo' AND post_status='publish'" );
		$with_taken = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON p.ID=m.post_id WHERE p.post_type='aplb_photo' AND p.post_status='publish' AND m.meta_key=%s AND m.meta_value<>''", APLB_META_TAKEN_DATE ) );
		$with_published = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON p.ID=m.post_id WHERE p.post_type='aplb_photo' AND p.post_status='publish' AND m.meta_key=%s AND m.meta_value<>''", APLB_META_PUBLISHED_DATE ) );
		$with_keywords = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p INNER JOIN {$wpdb->term_relationships} tr ON p.ID=tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id=tt.term_taxonomy_id WHERE p.post_type='aplb_photo' AND p.post_status='publish' AND tt.taxonomy=%s", 'aplb_keyword' ) );
		return [
			'total' => $total,
			'with_taken' => $with_taken,
			'with_published' => $with_published,
			'with_keywords' => $with_keywords,
		];
	}

	/**
	 * Handle admin actions triggered by button clicks.
	 *
	 * This method checks for the presence of action buttons in the POST data,
	 * verifies the associated nonces, and executes the corresponding action callbacks.
	 *
	 * @since    1.0.0
	 */
	public function handle_admin_actions() {
		$this->actions_manager->handle_actions();
	}

	/**
	 * (Deprecated handlers removed: auto-create and back-to-top now processed via handle_overview_settings.)
	 */

	/**
	 * Create a post on image upload if enabled in settings.
	 *
	 * @since    1.0.0
	 * @param    int      $image_id    The ID of the uploaded image.
	 */
	public function maybe_create_post_on_image_upload( $image_id ) {
		require_once plugin_dir_path(__FILE__) 	. 'services/PhotoPostCreator.php';
		$creator = new PhotoPostCreator();
		$creator->create_post_on_image_upload($image_id);
	}

	/**
	 * Show admin notices collected from actions manager and admin class.
	 *
	 * This method collects notices from both the actions manager and the admin class,
	 * then displays them in the WordPress admin area.
	 *
	 * @since    1.0.0
	 */
	public function show_admin_notices() {
		$notices = [];

		// Collect notice from actions manager
		if ( is_object( $this->actions_manager ) && method_exists( $this->actions_manager, 'get_last_notice' ) ) {
			$action_notice = $this->actions_manager->get_last_notice();
			if ( $action_notice ) {
				$notices[] = $action_notice;
				// (Notice already consumed; skip direct property reset for compatibility.)
			}
		}

		// Collect notice from bulk actions manager
		if ( is_object( $this->bulk_actions_manager ) && method_exists( $this->bulk_actions_manager, 'get_last_notice' ) ) {
			$bulk_notice = $this->bulk_actions_manager->get_last_notice();
			if ( $bulk_notice ) {
				$notices[] = $bulk_notice;
				// (Notice already consumed; skip direct property reset for compatibility.)
			}
		}

		// Collect notice from admin class itself
		if ($this->last_notice) {
			$notices[] = $this->last_notice;
			$this->last_notice = null;
		}

		// Display all collected notices
		foreach ($notices as $notice) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr($notice['type']),
				esc_html($notice['message'])
			);
		}
	}

	/**
	 * Add a thumbnail box to the quick edit interface for the aplb_photo post type.
	 *
	 * @since    1.0.0
	 * @param    string    $column_name    The name of the column being rendered.
	 * @param    string    $post_type      The post type of the current screen.
	 */
	public function add_quick_edit_thumbnail_box($column_name, $post_type) {
		if ($post_type !== 'aplb_photo' || $column_name !== 'thumbnail') return;
		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php _e('Thumbnail', 'ap-library'); ?></span>
					<span class="input-text-wrap">
						<span id="aplb-quickedit-thumbnail"></span>
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Register REST API routes for admin bulk operations.
	 *
	 * Registers the /ap-library/v1/assign-genres endpoint for bulk genre assignment.
	 *
	 * @since    1.3.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'ap-library/v1',
			'/assign-genres',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_assign_genres' ],
				'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
			]
		);

		// Portfolio bulk assignment route
		register_rest_route(
			'ap-library/v1',
			'/assign-portfolios',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_assign_portfolios' ],
				'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
			]
		);

		// Location bulk assignment route
		register_rest_route(
			'ap-library/v1',
			'/assign-locations',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_assign_locations' ],
				'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
			]
		);
		
		// Bulk date update route
		register_rest_route(
			'ap-library/v1',
			'/bulk-update-date',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_bulk_update_date' ],
				'permission_callback' => function() { return current_user_can( 'edit_posts' ); },
			]
		);
	}

	/**
	 * REST callback to assign selected genre terms to multiple photo posts.
	 *
	 * Supports two modes: 'add' (merge with existing genres) and 'replace' (overwrite existing genres).
	 * Expects JSON payload: { postIds: [], termIds: [], mode: 'add'|'replace' }
	 *
	 * @since    1.3.0
	 * @param    WP_REST_Request $request The REST request object containing postIds, termIds, and mode.
	 * @return   WP_REST_Response Response object with success status and list of updated post IDs.
	 */
	public function rest_assign_genres( WP_REST_Request $request ) {
		$post_ids = (array) $request->get_param( 'postIds' );
		$term_ids = (array) $request->get_param( 'termIds' );
		$mode     = $request->get_param( 'mode' );
		$post_ids = array_filter( array_map( 'intval', $post_ids ) );
		$term_ids = array_filter( array_map( 'intval', $term_ids ) );
		if ( empty( $post_ids ) || empty( $term_ids ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Missing post or term IDs.', 'ap-library' ) ], 400 );
		}
		$updated = [];
		foreach ( $post_ids as $pid ) {
			if ( get_post_type( $pid ) !== 'aplb_photo' || ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			if ( $mode === 'replace' ) {
				$result = wp_set_object_terms( $pid, $term_ids, 'aplb_genre' );
			} else {
				$current_terms = wp_get_object_terms( $pid, 'aplb_genre', [ 'fields' => 'ids' ] );
				if ( is_wp_error( $current_terms ) ) { $current_terms = []; }
				$new_terms = array_unique( array_merge( $current_terms, $term_ids ) );
				$result = wp_set_object_terms( $pid, $new_terms, 'aplb_genre' );
			}
			if ( ! is_wp_error( $result ) ) {
				$updated[] = $pid;
			}
		}
		return new WP_REST_Response( [ 'success' => true, 'updated' => $updated, 'mode' => ( $mode === 'replace' ? 'replace' : 'add' ) ], 200 );
	}

	/**
	 * Output the bulk genre toolbar on the photo list screen.
	 *
	 * Renders an inline toolbar with genre selection, add/replace mode toggle,
	 * and apply button for bulk genre assignment to selected photos.
	 *
	 * @since    1.3.0
	 */
	public function render_bulk_genre_toolbar() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'edit-aplb_photo' ) { return; }
		$terms = get_terms( [ 'taxonomy' => 'aplb_genre', 'hide_empty' => false ] );
		// Inline container; will be repositioned after Filter button via JS for proper order.
		?>
		<span id="aplb-inline-bulk-genres" class="aplb-inline-bulk-genres" style="display:inline-block; vertical-align:top; margin-left:12px; max-width:480px;">
			<label for="aplb-bulk-genre-select" style="font-weight:600; display:block; margin-bottom:2px;"><?php esc_html_e( 'Bulk Genres', 'ap-library' ); ?></label>
			<select multiple id="aplb-bulk-genre-select" style="width:180px; height:120px; margin-right:12px; float:left;">
				<?php foreach ( $terms as $t ) : ?>
					<option value="<?php echo esc_attr( $t->term_id ); ?>" data-name="<?php echo esc_attr( $t->name ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<div style="display:inline-block; width:260px;">
				<label style="display:block; margin:2px 0 4px;"><input type="checkbox" id="aplb-bulk-genre-replace" value="1" /> <?php esc_html_e( 'Replace existing genres', 'ap-library' ); ?></label>
				<button type="button" class="button" id="aplb-bulk-genre-apply" disabled style="margin-top:4px; display:inline-block;"><?php esc_html_e( 'Apply Genres to Selected', 'ap-library' ); ?></button>
				<span class="spinner" style="visibility:hidden; float:none; margin-top:6px;"></span>
				<span class="aplb-bulk-genre-status" style="display:block; min-height:16px; font-size:11px; margin-top:4px;" aria-live="polite"></span>
				<small style="display:block; color:#666; margin-top:4px; font-size:11px;"><?php esc_html_e( 'Add merges; Replace overwrites.', 'ap-library' ); ?></small>
			</div>
			<div style="clear:both;"></div>
		</span>
		<?php
	}

	/**
	 * Output the bulk portfolio toolbar on the photo list screen.
	 *
	 * Similar UI to genres, allows selecting portfolio terms and applying
	 * add/replace to selected photos.
	 *
	 * @since    1.3.1
	 */
	public function render_bulk_portfolio_toolbar() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'edit-aplb_photo' ) { return; }
		$terms = get_terms( [ 'taxonomy' => 'aplb_portfolio', 'hide_empty' => false ] );
		?>
		<span id="aplb-inline-bulk-portfolios" class="aplb-inline-bulk-portfolios" style="display:inline-block; vertical-align:top; margin-left:12px; max-width:480px;">
			<label for="aplb-bulk-portfolio-select" style="font-weight:600; display:block; margin-bottom:2px;"><?php esc_html_e( 'Bulk Portfolios', 'ap-library' ); ?></label>
			<select multiple id="aplb-bulk-portfolio-select" style="width:180px; height:120px; margin-right:12px; float:left;">
				<?php foreach ( $terms as $t ) : ?>
					<option value="<?php echo esc_attr( $t->term_id ); ?>" data-name="<?php echo esc_attr( $t->name ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<div style="display:inline-block; width:260px;">
				<label style="display:block; margin:2px 0 4px;"><input type="checkbox" id="aplb-bulk-portfolio-replace" value="1" /> <?php esc_html_e( 'Replace existing portfolios', 'ap-library' ); ?></label>
				<button type="button" class="button" id="aplb-bulk-portfolio-apply" disabled style="margin-top:4px; display:inline-block;"><?php esc_html_e( 'Apply Portfolios to Selected', 'ap-library' ); ?></button>
				<span class="spinner" style="visibility:hidden; float:none; margin-top:6px;"></span>
				<span class="aplb-bulk-portfolio-status" style="display:block; min-height:16px; font-size:11px; margin-top:4px;" aria-live="polite"></span>
				<small style="display:block; color:#666; margin-top:4px; font-size:11px;"><?php esc_html_e( 'Add merges; Replace overwrites.', 'ap-library' ); ?></small>
			</div>
			<div style="clear:both;"></div>
		</span>
		<?php
	}

	/**
	 * Render bulk location assignment toolbar.
	 *
	 * @since    1.4.0
	 */
	public function render_bulk_location_toolbar() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'edit-aplb_photo' ) { return; }
		$terms = get_terms( [ 'taxonomy' => 'aplb_location', 'hide_empty' => false ] );
		?>
		<span id="aplb-inline-bulk-locations" class="aplb-inline-bulk-locations" style="display:inline-block; vertical-align:top; margin-left:12px; max-width:480px;">
			<label for="aplb-bulk-location-select" style="font-weight:600; display:block; margin-bottom:2px;"><?php esc_html_e( 'Bulk Locations', 'ap-library' ); ?></label>
			<select multiple id="aplb-bulk-location-select" style="width:180px; height:120px; margin-right:12px; float:left;">
				<?php foreach ( $terms as $t ) : ?>
					<option value="<?php echo esc_attr( $t->term_id ); ?>" data-name="<?php echo esc_attr( $t->name ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
			<div style="display:inline-block; width:260px;">
				<label style="display:block; margin:2px 0 4px;"><input type="checkbox" id="aplb-bulk-location-replace" value="1" /> <?php esc_html_e( 'Replace existing locations', 'ap-library' ); ?></label>
				<button type="button" class="button" id="aplb-bulk-location-apply" disabled style="margin-top:4px; display:inline-block;"><?php esc_html_e( 'Apply Locations to Selected', 'ap-library' ); ?></button>
				<span class="spinner" style="visibility:hidden; float:none; margin-top:6px;"></span>
				<span class="aplb-bulk-location-status" style="display:block; min-height:16px; font-size:11px; margin-top:4px;" aria-live="polite"></span>
				<small style="display:block; color:#666; margin-top:4px; font-size:11px;"><?php esc_html_e( 'Add merges; Replace overwrites.', 'ap-library' ); ?></small>
			</div>
			<div style="clear:both;"></div>
		</span>
		<?php
	}

	/**
	 * REST callback to assign portfolio terms to multiple photo posts.
	 *
	 * @since    1.3.1
	 */
	public function rest_assign_portfolios( WP_REST_Request $request ) {
		$post_ids = (array) $request->get_param( 'postIds' );
		$term_ids = (array) $request->get_param( 'termIds' );
		$mode     = $request->get_param( 'mode' );
		$post_ids = array_filter( array_map( 'intval', $post_ids ) );
		$term_ids = array_filter( array_map( 'intval', $term_ids ) );
		if ( empty( $post_ids ) || empty( $term_ids ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Missing post or term IDs.', 'ap-library' ) ], 400 );
		}
		$updated = [];
		foreach ( $post_ids as $pid ) {
			if ( get_post_type( $pid ) !== 'aplb_photo' || ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			if ( $mode === 'replace' ) {
				$result = wp_set_object_terms( $pid, $term_ids, 'aplb_portfolio' );
			} else {
				$current_terms = wp_get_object_terms( $pid, 'aplb_portfolio', [ 'fields' => 'ids' ] );
				if ( is_wp_error( $current_terms ) ) { $current_terms = []; }
				$new_terms = array_unique( array_merge( $current_terms, $term_ids ) );
				$result = wp_set_object_terms( $pid, $new_terms, 'aplb_portfolio' );
			}
			if ( ! is_wp_error( $result ) ) {
				$updated[] = $pid;
			}
		}
		return new WP_REST_Response( [ 'success' => true, 'updated' => $updated, 'mode' => ( $mode === 'replace' ? 'replace' : 'add' ) ], 200 );
	}

	/**
	 * REST callback to assign location terms to multiple photo posts.
	 *
	 * @since    1.4.0
	 */
	public function rest_assign_locations( WP_REST_Request $request ) {
		$post_ids = (array) $request->get_param( 'postIds' );
		$term_ids = (array) $request->get_param( 'termIds' );
		$mode     = $request->get_param( 'mode' );
		$post_ids = array_filter( array_map( 'intval', $post_ids ) );
		$term_ids = array_filter( array_map( 'intval', $term_ids ) );
		if ( empty( $post_ids ) || empty( $term_ids ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => __( 'Missing post or term IDs.', 'ap-library' ) ], 400 );
		}
		$updated = [];
		foreach ( $post_ids as $pid ) {
			if ( get_post_type( $pid ) !== 'aplb_photo' || ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			if ( $mode === 'replace' ) {
				$result = wp_set_object_terms( $pid, $term_ids, 'aplb_location' );
			} else {
				$current_terms = wp_get_object_terms( $pid, 'aplb_location', [ 'fields' => 'ids' ] );
				if ( is_wp_error( $current_terms ) ) { $current_terms = []; }
				$new_terms = array_unique( array_merge( $current_terms, $term_ids ) );
				$result = wp_set_object_terms( $pid, $new_terms, 'aplb_location' );
			}
			if ( ! is_wp_error( $result ) ) {
				$updated[] = $pid;
			}
		}
		return new WP_REST_Response( [ 'success' => true, 'updated' => $updated, 'mode' => ( $mode === 'replace' ? 'replace' : 'add' ) ], 200 );
	}

	/**
	 * Output the bulk post date toolbar on the photo list screen.
	 *
	 * Renders an inline toolbar with date component selectors (year, month, day, hour, minute)
	 * that allows updating specific parts of post_date for selected photos.
	 *
	 * @since    1.3.2
	 */
	public function render_bulk_date_toolbar() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'edit-aplb_photo' ) { return; }
		
		$current_year = (int) date('Y');
		?>
		<div id="aplb-inline-bulk-date" class="aplb-inline-bulk-date" style="display:inline-block; vertical-align:top; margin-left:12px; max-width:800px; border-left: 1px solid #ddd; padding-left:12px;">
			<label style="font-weight:600; display:block; margin-bottom:4px;"><?php esc_html_e( 'Bulk Date Update', 'ap-library' ); ?></label>
			
			<!-- Date Type Selection -->
			<div style="margin-bottom:8px; padding:6px; background:#f0f0f1; border-radius:3px;">
				<label style="font-size:11px; font-weight:600; display:block; margin-bottom:4px;"><?php esc_html_e( 'Date Type:', 'ap-library' ); ?></label>
				<label style="margin-right:16px; font-size:12px;">
					<input type="radio" name="aplb-bulk-date-type" value="post_date" checked />
					<?php esc_html_e( 'Post Date', 'ap-library' ); ?>
				</label>
				<label style="margin-right:16px; font-size:12px;">
					<input type="radio" name="aplb-bulk-date-type" value="published_date" />
					<?php esc_html_e( 'Published Date', 'ap-library' ); ?>
				</label>
				<label style="font-size:12px;">
					<input type="radio" name="aplb-bulk-date-type" value="taken_date" />
					<?php esc_html_e( 'Taken Date', 'ap-library' ); ?>
				</label>
			</div>
			
			<div style="display:flex; gap:8px; align-items:flex-start; flex-wrap:wrap;">
				<!-- Year -->
				<div style="display:inline-block;">
					<label style="display:block; margin-bottom:2px; font-size:11px;">
						<input type="checkbox" id="aplb-bulk-date-year-enable" value="1" />
						<?php esc_html_e( 'Year', 'ap-library' ); ?>
					</label>
					<select id="aplb-bulk-date-year" disabled style="width:80px;">
						<?php for ($y = $current_year + 5; $y >= $current_year - 50; $y--) : ?>
							<option value="<?php echo esc_attr($y); ?>" <?php selected($y, $current_year); ?>><?php echo esc_html($y); ?></option>
						<?php endfor; ?>
					</select>
				</div>
				
				<!-- Month -->
				<div style="display:inline-block;">
					<label style="display:block; margin-bottom:2px; font-size:11px;">
						<input type="checkbox" id="aplb-bulk-date-month-enable" value="1" />
						<?php esc_html_e( 'Month', 'ap-library' ); ?>
					</label>
					<select id="aplb-bulk-date-month" disabled style="width:70px;">
						<?php for ($m = 1; $m <= 12; $m++) : ?>
							<option value="<?php echo esc_attr($m); ?>"><?php echo esc_html(str_pad($m, 2, '0', STR_PAD_LEFT)); ?></option>
						<?php endfor; ?>
					</select>
				</div>
				
				<!-- Day -->
				<div style="display:inline-block;">
					<label style="display:block; margin-bottom:2px; font-size:11px;">
						<input type="checkbox" id="aplb-bulk-date-day-enable" value="1" />
						<?php esc_html_e( 'Day', 'ap-library' ); ?>
					</label>
					<select id="aplb-bulk-date-day" disabled style="width:70px;">
						<?php for ($d = 1; $d <= 31; $d++) : ?>
							<option value="<?php echo esc_attr($d); ?>"><?php echo esc_html(str_pad($d, 2, '0', STR_PAD_LEFT)); ?></option>
						<?php endfor; ?>
					</select>
				</div>
				
				<!-- Hour -->
				<div style="display:inline-block;">
					<label style="display:block; margin-bottom:2px; font-size:11px;">
						<input type="checkbox" id="aplb-bulk-date-hour-enable" value="1" />
						<?php esc_html_e( 'Hour', 'ap-library' ); ?>
					</label>
					<select id="aplb-bulk-date-hour" disabled style="width:70px;">
						<?php for ($h = 0; $h <= 23; $h++) : ?>
							<option value="<?php echo esc_attr($h); ?>"><?php echo esc_html(str_pad($h, 2, '0', STR_PAD_LEFT)); ?></option>
						<?php endfor; ?>
					</select>
				</div>
				
				<!-- Minute -->
				<div style="display:inline-block;">
					<label style="display:block; margin-bottom:2px; font-size:11px;">
						<input type="checkbox" id="aplb-bulk-date-minute-enable" value="1" />
						<?php esc_html_e( 'Minute', 'ap-library' ); ?>
					</label>
					<select id="aplb-bulk-date-minute" disabled style="width:70px;">
						<?php for ($min = 0; $min <= 59; $min++) : ?>
							<option value="<?php echo esc_attr($min); ?>"><?php echo esc_html(str_pad($min, 2, '0', STR_PAD_LEFT)); ?></option>
						<?php endfor; ?>
					</select>
				</div>
				
				<!-- Action buttons -->
				<div style="display:inline-block; margin-top:18px;">
					<button type="button" class="button" id="aplb-bulk-date-apply" disabled><?php esc_html_e( 'Update Dates', 'ap-library' ); ?></button>
					<span class="spinner" style="visibility:hidden; float:none; margin:0 0 0 4px;"></span>
				</div>
			</div>
			<div style="margin-top:4px;">
				<span class="aplb-bulk-date-status" style="display:block; min-height:16px; font-size:11px;" aria-live="polite"></span>
				<small style="display:block; color:#666; font-size:11px;"><?php esc_html_e( 'Select date type, check components to update, then select photos and click Update.', 'ap-library' ); ?></small>
			</div>
		</div>
		<?php
	}

	/**
	 * REST callback to bulk update post dates with partial component updates.
	 *
	 * Updates only specified date components (year, month, day, hour, minute) while keeping
	 * other components unchanged for each selected photo. Also syncs published_date and taken_date
	 * with their respective taxonomies.
	 *
	 * @since    1.3.2
	 * @param    WP_REST_Request $request The REST request object containing postIds and components.
	 * @return   WP_REST_Response Response object with success status and list of updated posts.
	 */
	public function rest_bulk_update_date( WP_REST_Request $request ) {
		$post_ids = (array) $request->get_param( 'postIds' );
		$components = (array) $request->get_param( 'components' );
		$date_type = $request->get_param( 'dateType' ); // 'post_date', 'published_date', or 'taken_date'
		
		$post_ids = array_filter( array_map( 'intval', $post_ids ) );
		
		if ( empty( $post_ids ) || empty( $components ) ) {
			return new WP_REST_Response( [ 
				'success' => false, 
				'message' => __( 'Missing post IDs or date components.', 'ap-library' ) 
			], 400 );
		}
		
		// Validate date type
		if ( ! in_array( $date_type, [ 'post_date', 'published_date', 'taken_date' ], true ) ) {
			$date_type = 'post_date';
		}
		
		// Get meta box instance for syncing dates to taxonomies
		require_once plugin_dir_path( __FILE__ ) . 'class-ap-library-meta-box.php';
		$meta_box = new Ap_Library_Meta_Box();
		
		$updated = [];
		$errors = [];
		
		foreach ( $post_ids as $pid ) {
			if ( get_post_type( $pid ) !== 'aplb_photo' || ! current_user_can( 'edit_post', $pid ) ) {
				continue;
			}
			
			$post = get_post( $pid );
			if ( ! $post ) {
				continue;
			}
			
			// Get current date based on type
			if ( $date_type === 'post_date' ) {
				$current_date = $post->post_date;
			} elseif ( $date_type === 'published_date' ) {
				$current_date = get_post_meta( $pid, APLB_META_PUBLISHED_DATE, true );
				if ( empty( $current_date ) ) {
					$current_date = gmdate( 'Y-m-d', strtotime( $post->post_date ) );
				}
				// Add time component if not present (for meta fields stored as date only)
				if ( strlen( $current_date ) === 10 ) {
					$current_date .= ' 00:00:00';
				}
			} else { // taken_date
				$current_date = get_post_meta( $pid, APLB_META_TAKEN_DATE, true );
				if ( empty( $current_date ) ) {
					$current_date = gmdate( 'Y-m-d', strtotime( $post->post_date ) );
				}
				// Add time component if not present
				if ( strlen( $current_date ) === 10 ) {
					$current_date .= ' 00:00:00';
				}
			}
			
			// Parse current date
			$date_parts = date_parse( $current_date );
			
			// Apply new components
			$new_year   = isset( $components['year'] ) ? (int) $components['year'] : $date_parts['year'];
			$new_month  = isset( $components['month'] ) ? (int) $components['month'] : $date_parts['month'];
			$new_day    = isset( $components['day'] ) ? (int) $components['day'] : $date_parts['day'];
			$new_hour   = isset( $components['hour'] ) ? (int) $components['hour'] : $date_parts['hour'];
			$new_minute = isset( $components['minute'] ) ? (int) $components['minute'] : $date_parts['minute'];
			$new_second = $date_parts['second']; // Keep seconds unchanged
			
			// Validate date components
			if ( ! checkdate( $new_month, $new_day, $new_year ) ) {
				$errors[] = sprintf( 
					__( 'Invalid date for post #%d: %04d-%02d-%02d', 'ap-library' ), 
					$pid, $new_year, $new_month, $new_day 
				);
				continue;
			}
			
			// Build new date string
			$new_date_full = sprintf(
				'%04d-%02d-%02d %02d:%02d:%02d',
				$new_year,
				$new_month,
				$new_day,
				$new_hour,
				$new_minute,
				$new_second
			);
			
			// Build date-only string for meta fields
			$new_date_string = sprintf( '%04d-%02d-%02d', $new_year, $new_month, $new_day );
			
			// Update based on date type
			if ( $date_type === 'post_date' ) {
				// Convert to GMT for storage
				$new_date_gmt = get_gmt_from_date( $new_date_full );
				
				// Update post
				$result = wp_update_post( [
					'ID'            => $pid,
					'post_date'     => $new_date_full,
					'post_date_gmt' => $new_date_gmt,
				], true );
				
				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf( 
						__( 'Failed to update post #%d: %s', 'ap-library' ), 
						$pid, $result->get_error_message() 
					);
					continue;
				}
			} elseif ( $date_type === 'published_date' ) {
				// Update meta field
				update_post_meta( $pid, APLB_META_PUBLISHED_DATE, $new_date_string );
				
				// Sync to taxonomy
				$meta_box->sync_date_to_taxonomy( $pid, $new_date_string, 'aplb_published_date' );
			} else { // taken_date
				// Update meta field
				update_post_meta( $pid, APLB_META_TAKEN_DATE, $new_date_string );
				
				// Sync to taxonomy
				$meta_box->sync_date_to_taxonomy( $pid, $new_date_string, 'aplb_taken_date' );
			}
			
			$updated[] = [
				'postId'  => $pid,
				'newDate' => $new_date_full,
			];
		}
		
		if ( ! empty( $updated ) ) {
			return new WP_REST_Response( [ 
				'success' => true, 
				'updated' => $updated,
				'errors'  => $errors,
			], 200 );
		} else {
			return new WP_REST_Response( [ 
				'success' => false, 
				'message' => __( 'No posts were updated.', 'ap-library' ),
				'errors'  => $errors,
			], 400 );
		}
	}

	/**
	 * Render taxonomy filter dropdowns for the photo list view.
	 *
	 * Adds filter dropdowns for all photo taxonomies (genre, portfolio, 
	 * keywords, location, taken date, published date) above the photo list table.
	 *
	 * @since    1.4.0
	 */
	public function render_taxonomy_filters() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->id !== 'edit-aplb_photo' ) {
			return;
		}

		// Define all filterable taxonomies
		$taxonomies = [
			'aplb_genre'          => __( 'Photo Genre', 'ap-library' ),
			'aplb_portfolio'      => __( 'Portfolios', 'ap-library' ),
			'aplb_keyword'        => __( 'Photo Keywords', 'ap-library' ),
			'aplb_location'       => __( 'Photo Locations', 'ap-library' ),
			'aplb_taken_date'     => __( 'Taken Date', 'ap-library' ),
			'aplb_published_date' => __( 'Published Date', 'ap-library' ),
		];

		foreach ( $taxonomies as $taxonomy_slug => $taxonomy_label ) {
			$selected = isset( $_GET[ $taxonomy_slug ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy_slug ] ) ) : '';

			$terms = get_terms( [
				'taxonomy'   => $taxonomy_slug,
				'hide_empty' => true,
			] );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			?>
			<select name="<?php echo esc_attr( $taxonomy_slug ); ?>" id="<?php echo esc_attr( $taxonomy_slug ); ?>-filter">
				<option value=""><?php echo esc_html( $taxonomy_label ); ?></option>
				<?php
				foreach ( $terms as $term ) {
					?>
					<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected, $term->slug ); ?>>
						<?php echo esc_html( $term->name ) . ' (' . intval( $term->count ) . ')'; ?>
					</option>
					<?php
				}
				?>
			</select>
			<?php
		}
	}

}


