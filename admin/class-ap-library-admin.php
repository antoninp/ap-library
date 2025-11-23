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
			'aplb_photo_page_ap-library-overview',
			'aplb_photo_page_aplb-backfill',
			'aplb_photo_page_aplb-archive-settings',
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
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url(__FILE__) . 'js/ap-library-admin.js',
				['jquery'],
				$this->version,
				false
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
			'ap-library-overview',
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
				<?php $this->render_setting_auto_create(); ?>
				<?php $this->render_setting_back_to_top(); ?>
			</div>

			<h2><?php esc_html_e( 'Tools & Settings Links', 'ap-library' ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aplb_photo&page=aplb-backfill' ) ); ?>"><?php esc_html_e( 'Backfill Tools', 'ap-library' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aplb_photo&page=aplb-archive-settings' ) ); ?>"><?php esc_html_e( 'Archive Query Settings', 'ap-library' ); ?></a></li>
				<li><a href="https://wordpress.org/support/plugin/ap-library" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation & Support', 'ap-library' ); ?></a></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render auto-create setting form.
	 */
	private function render_setting_auto_create() {
		$enabled = get_option( 'ap_library_auto_create_post_on_upload', false );
		?>
		<form method="post" style="margin-bottom:1em;">
			<?php wp_nonce_field( 'ap_library_auto_create_post_on_upload_action', 'ap_library_auto_create_post_on_upload_nonce' ); ?>
			<label for="ap_library_auto_create_post_on_upload">
				<input type="checkbox" id="ap_library_auto_create_post_on_upload" name="ap_library_auto_create_post_on_upload" value="1" <?php checked( $enabled, true ); ?> />
				<?php esc_html_e( 'Automatically create a photo post when an image is uploaded', 'ap-library' ); ?>
			</label>
			<p><input type="submit" value="<?php esc_attr_e( 'Save', 'ap-library' ); ?>" class="button button-primary" /></p>
		</form>
		<?php
	}

	/**
	 * Render back-to-top setting form.
	 */
	private function render_setting_back_to_top() {
		$enabled = get_option( 'ap_library_enable_back_to_top', false );
		?>
		<form method="post" style="margin-bottom:1em;">
			<?php wp_nonce_field( 'ap_library_enable_back_to_top_action', 'ap_library_enable_back_to_top_nonce' ); ?>
			<label for="ap_library_enable_back_to_top">
				<input type="checkbox" id="ap_library_enable_back_to_top" name="ap_library_enable_back_to_top" value="1" <?php checked( $enabled, true ); ?> />
				<?php esc_html_e( 'Enable "Back to Top" button on public photo pages', 'ap-library' ); ?>
			</label>
			<p><input type="submit" value="<?php esc_attr_e( 'Save', 'ap-library' ); ?>" class="button button-primary" /></p>
		</form>
		<?php
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
	 * Handle the "Auto Create Post on Upload" option.
	 *
	 * This method checks if the nonce is set and valid, then updates the option
	 * to enable or disable automatic post creation on image upload based on the form submission.
	 */
	public function handle_auto_create_post_option() {
		if (
			isset( $_POST['ap_library_auto_create_post_on_upload_nonce'] ) &&
			wp_verify_nonce( $_POST['ap_library_auto_create_post_on_upload_nonce'], 'ap_library_auto_create_post_on_upload_action' )
		) {
			$enabled = isset( $_POST['ap_library_auto_create_post_on_upload'] ) ? true : false;
			update_option( 'ap_library_auto_create_post_on_upload', $enabled );
			$this->last_notice = [
				'type' => 'success',
				'message' => __( 'Settings saved.', 'ap-library' )
			];
		}
	}

	/**
	 * Handle the "Back to Top" button option.
	 *
	 * This method checks if the nonce is set and valid, then updates the option
	 * to enable or disable the "Back to Top" button based on the form submission.
	 */
	public function handle_back_to_top_option() {
		if (
			isset( $_POST['ap_library_enable_back_to_top_nonce'] ) &&
			wp_verify_nonce( $_POST['ap_library_enable_back_to_top_nonce'], 'ap_library_enable_back_to_top_action' )
		) {
			$enabled = isset( $_POST['ap_library_enable_back_to_top'] ) ? true : false;
			update_option( 'ap_library_enable_back_to_top', $enabled );
			$this->last_notice = [
				'type' => 'success',
				'message' => __( 'Settings saved.', 'ap-library' )
			];
		}
	}

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

}

