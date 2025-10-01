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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ap-library-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/ap-library-admin.js',
			array('jquery'),
			$this->version,
			false
		);
	}

	/**
	 * Register the admin menu for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'AP Library', 'ap-library' ),
			__( 'AP Library', 'ap-library' ),
			'manage_options',
			'ap-library',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-open-folder',
			25
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
		require_once plugin_dir_path(__FILE__) . 'services/UploadPostCreator.php';
		$creator = new UploadPostCreator();
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
		if ($this->actions_manager && $this->actions_manager->get_last_notice()) {
			$notices[] = $this->actions_manager->get_last_notice();
			$this->actions_manager->last_notice = null;
		}

		// Collect notice from bulk actions manager
		if ($this->bulk_actions_manager && $this->bulk_actions_manager->get_last_notice()) {
			$notices[] = $this->bulk_actions_manager->get_last_notice();
			$this->bulk_actions_manager->last_notice = null;
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
	 * Add a thumbnail box to the quick edit interface for the aplb_uploads post type.
	 *
	 * @since    1.0.0
	 * @param    string    $column_name    The name of the column being rendered.
	 * @param    string    $post_type      The post type of the current screen.
	 */
	public function add_quick_edit_thumbnail_box($column_name, $post_type) {
		if ($post_type !== 'aplb_uploads' || $column_name !== 'thumbnail') return;
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

