<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    AP_Library
 * @subpackage AP_Library/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AP_Library
 * @subpackage AP_Library/admin
 * @author     Antonin Puleo <
 */
class AP_Library_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ap_library    The ID of this plugin.
	 */
	private $ap_library;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $ap_library       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $ap_library, $version ) {

		$this->ap_library = $ap_library;
		$this->version = $version;

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
		 * defined in AP_Library_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AP_Library_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->ap_library, plugin_dir_url( APLB_ENTRY ) . 'css/' . APLB_DOMAIN . '-admin.css', array(), $this->version, 'all' );

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
		 * defined in AP_Library_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AP_Library_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->ap_library, plugin_dir_url( APLB_ENTRY ) . 'js/' . APLB_DOMAIN . '-admin.js', array( 'jquery' ), $this->version, false );

	}

}
