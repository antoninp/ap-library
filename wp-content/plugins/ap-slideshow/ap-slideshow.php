<?php
/*
Plugin Name: AP Slideshow
Description: A Gutenberg block for a fade/slide/zoom image gallery with captions, dots, and links.
Author: Antonin Puleo
Version: 1.0.0
Text Domain: ap-slideshow
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-ap-slideshow-block.php';

function ap_slideshow_register_block() {
    $block = new Ap_Slideshow_Block();
    $block->register_block();
}
add_action( 'init', 'ap_slideshow_register_block' );

// Enqueue public assets
function ap_slideshow_enqueue_assets() {
    wp_enqueue_style(
        'ap-slideshow-public',
        plugins_url( 'public/css/ap-slideshow-public.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'public/css/ap-slideshow-public.css' )
    );
    wp_enqueue_script(
        'ap-slideshow-public',
        plugins_url( 'public/js/ap-slideshow-public.js', __FILE__ ),
        array('jquery'),
        filemtime( plugin_dir_path( __FILE__ ) . 'public/js/ap-slideshow-public.js' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ap_slideshow_enqueue_assets' );