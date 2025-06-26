<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ap_Library_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AP_LIBRARY_VERSION' ) ) {
			$this->version = AP_LIBRARY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ap-library';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_custom_post_type_hooks();
		$this->define_taxonomy_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ap_Library_Loader. Orchestrates the hooks of the plugin.
	 * - Ap_Library_i18n. Defines internationalization functionality.
	 * - Ap_Library_Admin. Defines all hooks for the admin area.
	 * - Ap_Library_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ap-library-public.php';

		/**
         * The class responsible for defining custom post types.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-cpt.php';

		/**
         * The class responsible for defining taxonomies.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-taxonomy.php';


		$this->loader = new Ap_Library_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ap_Library_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ap_Library_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ap_Library_Admin( $this->get_plugin_name(), $this->get_version() );
		
		// Main admin hooks
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_admin_actions' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_auto_create_post_option' );
		$this->loader->add_action( 'add_attachment', $plugin_admin, 'maybe_create_post_on_image_upload' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_back_to_top_option' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_admin_notices' );

		// Columns hooks
		$columns_manager = $plugin_admin->get_columns_manager();
		$this->loader->add_filter( 'manage_aplb_uploads_posts_columns', $columns_manager, 'add_aplb_uploads_thumbnail_column' );
		$this->loader->add_action( 'manage_aplb_uploads_posts_custom_column', $columns_manager, 'render_aplb_uploads_thumbnail_column', 10, 2 );
		
		// Bulk actions hooks
		$bulk_actions_manager = $plugin_admin->get_bulk_actions_manager();
		$this->loader->add_filter( 'bulk_actions-edit-aplb_uploads', $bulk_actions_manager, 'register_uploads_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-aplb_uploads', $bulk_actions_manager, 'handle_uploads_bulk_action', 10, 3 );
		$this->loader->add_action( 'admin_init', $bulk_actions_manager, 'maybe_set_bulk_action_notice' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ap_Library_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'maybe_add_back_to_top_button' );

	}

	/**
     * Register all of the hooks related to custom post types.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_custom_post_type_hooks() {

        $plugin_custom_post_types = new Ap_Library_Custom_Post_Types();
		
        $this->loader->add_action( 'init', $plugin_custom_post_types, 'register_post_types' );
    }

	/**
     * Register all of the hooks related to custom post types.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_taxonomy_hooks() {

        $plugin_taxonomies = new Ap_Library_Taxonomy();
		
        $this->loader->add_action( 'init', $plugin_taxonomies, 'register_taxonomies' );
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ap_Library_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
