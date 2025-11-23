<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://antoninpuleo.com
 * @since             1.0.0
 * @package           Ap_Library
 *
 * @wordpress-plugin
 * Plugin Name:       AP Library
 * Plugin URI:        https://antoninpuleo.com
 * Description:       Photo Library System based on post for photography website.
 * Version:           1.3.0
 * Author:            Antonin Puleo
 * Author URI:        https://antoninpuleo.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ap-library
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'AP_LIBRARY_VERSION', '1.3.0' );

/**
 * Meta key constants for date fields.
 */
define( 'APLB_META_PUBLISHED_DATE', 'aplb_published_date' );
define( 'APLB_META_TAKEN_DATE', 'aplb_taken_date' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ap-library-activator.php
 */
function activate_ap_library() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ap-library-activator.php';
	Ap_Library_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ap-library-deactivator.php
 */
function deactivate_ap_library() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ap-library-deactivator.php';
	Ap_Library_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ap_library' );
register_deactivation_hook( __FILE__, 'deactivate_ap_library' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ap-library.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ap_library() {

	$plugin = new Ap_Library();
	$plugin->run();

}
run_ap_library();

