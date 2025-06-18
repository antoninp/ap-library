<?php
/**
 * Plugin Name:       Antonin Puleo Library
 * Description:       Photo Library System based on post for photography website.
 * Version:           0.0.1
 * Author:            Antonin Puleo
 * Author URI:        https://antoninpuleo.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ap-library
 * Domain Path:       /languages
 * Requires Plugins:  meow-gallery
 */

if ( !defined( 'APLB_VERSION' ) ) {
  define( 'APLB_VERSION', '0.0.1' );
  define( 'APLB_PREFIX', 'aplb' );
  define( 'APLB_DOMAIN', ' ap-library' );
  define( 'APLB_ENTRY', __FILE__ );
  define( 'APLB_PATH', dirname( __FILE__ ) );
  define( 'APLB_URL', plugin_dir_url( __FILE__ ) );
  define( 'APLB_ITEM_ID', 6242 );
}

require_once( 'classes/init.php');

?>