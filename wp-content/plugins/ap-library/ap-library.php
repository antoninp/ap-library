<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://antoninpuleo.com/
 * @since             1.0.0
 * @package           AP_Library
 *
 * @wordpress-plugin
 * Plugin Name:       AP Library
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Photo Library System based on post for photography website.
 * Version:           1.0.0
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

if ( ! defined( 'APLB_VERSION' ) ) {
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
  define( 'APLB_VERSION', '1.0.0' );

  define( 'APLB_PREFIX', 'aplb' );
  define( 'APLB_DOMAIN', 'ap-library' );
  define( 'APLB_ENTRY', __FILE__ );
  define( 'APLB_PATH', dirname( __FILE__ ) );
  define( 'APLB_URL', plugin_dir_url( __FILE__ ) );
  define( 'APLB_ITEM_ID', 6242 );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ap-library-activator.php
 */
function activate_ap_library() {
	require_once plugin_dir_path( APLB_ENTRY ) . 'includes/class-' . APLB_DOMAIN . '-activator.php';
	AP_Library_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ap-library-deactivator.php
 */
function deactivate_ap_library() {
	require_once plugin_dir_path( APLB_ENTRY ) . 'includes/class-' . APLB_DOMAIN . '-deactivator.php';
	AP_Library_Deactivator::deactivate();
}

register_activation_hook( APLB_ENTRY, 'activate_ap_library' );
register_deactivation_hook( APLB_ENTRY, 'deactivate_ap_library' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( APLB_ENTRY ) . 'includes/class-' . APLB_DOMAIN . '.php';

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

	$plugin = new AP_Library();
	$plugin->run();

}
run_ap_library();


?>