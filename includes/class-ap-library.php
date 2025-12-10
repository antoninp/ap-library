<?php

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
	 * - Ap_Library_Custom_Post_Types. Defines custom post types.
	 * - Ap_Library_Taxonomy. Defines taxonomies.
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

		/**
		 * The class responsible for EXIF extraction.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-exif.php';

		/**
		 * The class responsible for meta boxes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-meta-box.php';

		/**
		 * The class responsible for backfill tool.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-backfill.php';

		/**
		 * The class responsible for archive rules (configurable query rules).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-archive-rules.php';

		/**
		 * The class responsible for portfolio features (term meta and ordering).
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-portfolio.php';

		/**
		 * The class responsible for query modifications.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ap-library-query.php';

	
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

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

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
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_admin_actions' );
		// Unified settings handler replaces legacy individual handlers.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_overview_settings' );
		$this->loader->add_action( 'add_attachment', $plugin_admin, 'maybe_create_post_on_image_upload' );
		// (Deprecated handlers kept for backward compatibility but no longer hooked.)
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_admin_notices' );
		$this->loader->add_action('quick_edit_custom_box', $plugin_admin, 'add_quick_edit_thumbnail_box', 10, 2);
		// Bulk genre assignment toolbar (list table) & REST routes
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'render_bulk_genre_toolbar' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_routes' );
		// Bulk portfolio assignment toolbar
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'render_bulk_portfolio_toolbar' );
		// Bulk location assignment toolbar
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'render_bulk_location_toolbar' );
		// Bulk post date editor toolbar
		$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'render_bulk_date_toolbar' );

		// Columns hooks
		$columns_manager = $plugin_admin->get_columns_manager();
		$this->loader->add_filter( 'manage_aplb_photo_posts_columns', $columns_manager, 'add_aplb_photo_thumbnail_column' );
		$this->loader->add_action( 'manage_aplb_photo_posts_custom_column', $columns_manager, 'render_aplb_photo_thumbnail_column', 10, 2 );
		$this->loader->add_filter( 'manage_edit-aplb_photo_sortable_columns', $columns_manager, 'make_date_columns_sortable' );
		$this->loader->add_action( 'pre_get_posts', $columns_manager, 'handle_date_column_sorting' );
		$this->loader->add_action( 'quick_edit_custom_box', $columns_manager, 'add_quick_edit_date_fields', 10, 2 );
		$this->loader->add_action( 'save_post', $columns_manager, 'save_quick_edit_data' );
		$this->loader->add_action( 'admin_footer', $columns_manager, 'enqueue_quick_edit_script' );
		
		// Bulk actions hooks
		$bulk_actions_manager = $plugin_admin->get_bulk_actions_manager();
		$this->loader->add_filter( 'bulk_actions-edit-aplb_photo', $bulk_actions_manager, 'register_photo_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-aplb_photo', $bulk_actions_manager, 'handle_photo_bulk_action', 10, 3 );
		$this->loader->add_action( 'admin_init', $bulk_actions_manager, 'maybe_set_bulk_action_notice' );

		// Meta box hooks
		$meta_box = new Ap_Library_Meta_Box();
		$this->loader->add_action( 'add_meta_boxes', $meta_box, 'register_meta_boxes' );
		$this->loader->add_action( 'save_post', $meta_box, 'save_meta_box' );
		$this->loader->add_action( 'wp_ajax_aplb_extract_exif', $meta_box, 'ajax_extract_exif' );

		// Backfill tool hooks
		$backfill = new Ap_Library_Backfill();
		$this->loader->add_action( 'admin_menu', $backfill, 'add_backfill_submenu' );

		// Archive rules hooks
		$archive_rules = new Ap_Library_Archive_Rules();
		$this->loader->add_action( 'admin_menu', $archive_rules, 'add_rules_submenu' );
		$this->loader->add_action( 'admin_init', $archive_rules, 'register_rules' );

		// Portfolio hooks (term meta for featured images and menu order for photos)
		$portfolio = new Ap_Library_Portfolio();
		$this->loader->add_action( 'aplb_portfolio_edit_form_fields', $portfolio, 'add_portfolio_term_fields', 10, 1 );
		$this->loader->add_action( 'aplb_portfolio_add_form_fields', $portfolio, 'add_portfolio_term_fields_new' );
		$this->loader->add_action( 'edited_aplb_portfolio', $portfolio, 'save_portfolio_term_meta', 10, 1 );
		$this->loader->add_action( 'created_aplb_portfolio', $portfolio, 'save_portfolio_term_meta', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $portfolio, 'enqueue_portfolio_admin_scripts' );

		// Overview menu at bottom for better UX (after task-specific tools)
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu', 99 );
		
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

		// Query modification hooks
		$query_modifier = new Ap_Library_Query();
		$this->loader->add_action( 'pre_get_posts', $query_modifier, 'modify_archive_query' );
		$this->loader->add_filter( 'query_vars', $query_modifier, 'add_query_vars' );

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
