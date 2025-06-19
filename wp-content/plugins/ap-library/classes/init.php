<?php
/**
 * Initialization file for the AP Library plugin.
 */

 /**
 * Register the "uploads" and "library" custom post type
 */
function aplb_setup_post_type() {
	register_post_type( 'aplb_uploads', ['public' => true ] ); 
    register_post_type( 'aplb_library', ['public' => true ] );
} 
add_action( 'init', 'aplb_setup_post_type' );


/**
 * Activate the plugin.
 */
function aplb_activate() { 
	// Trigger our function that registers the custom post type plugin.
	aplb_setup_post_type(); 
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules(); 
}
register_activation_hook( APLB_ENTRY, 'aplb_activate' );

/**
 * Deactivation hook.
 */
function aplb_deactivate() {
	// Unregister the post type, so the rules are no longer in memory.
	unregister_post_type( 'aplb_uploads' );
    unregister_post_type( 'aplb_library' );
	// Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
}
register_deactivation_hook( APLB_ENTRY, 'aplb_deactivate' );

?>