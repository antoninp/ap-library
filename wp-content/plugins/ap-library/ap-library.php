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

 /**
 * Register the "uploads" custom post type
 */
function pluginprefix_setup_post_type() {
	register_post_type( 'uploads', ['public' => true ] ); 
    register_post_type( 'library', ['public' => true ] );
} 
add_action( 'init', 'pluginprefix_setup_post_type' );


/**
 * Activate the plugin.
 */
function pluginprefix_activate() { 
	// Trigger our function that registers the custom post type plugin.
	pluginprefix_setup_post_type(); 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'pluginprefix_activate' );