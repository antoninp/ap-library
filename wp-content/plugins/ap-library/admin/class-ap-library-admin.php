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
		$this->actions_manager = new Ap_Library_Admin_Actions();
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
		echo '<div class="wrap"><h1>' . esc_html__( 'AP Library Admin', 'ap-library' ) . '</h1>';
		$this->actions_manager->render_buttons();
		echo '</div>';
	}

	public function handle_admin_actions() {
		$this->actions_manager->handle_actions();
	}

	// Example action callbacks
	public function run_first_action() {
		// ...your code...
		return true;
	}

	public function run_second_action() {
		// ...your code...
		// Example error:
		// return new WP_Error('ap_library_error', 'Something went wrong.');
		return true;
	}

}
