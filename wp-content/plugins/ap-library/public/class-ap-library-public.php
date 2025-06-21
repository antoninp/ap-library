<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/public
 * @author     Antonin Puleo <a@antoninpuleo.com>
 */
class Ap_Library_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ap-library-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ap-library-public.js', array( 'jquery' ), $this->version, false );

	}

	public function maybe_add_back_to_top_button() {
		if ( ! get_option( 'ap_library_enable_back_to_top', false ) ) {
			return;
		}

		$location = 'right'; // or 'left'
		$scale = '1.5';
		$icon = '
				<svg class="w-[48px] h-[48px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
				<path stroke="#AEACA6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 15 7-7 7 7"/>
				</svg>
				';
		?>
			<a href="#" class="back-to-top"><div><?= $icon ?></div></a>
			<style>
			.back-to-top svg {
				transform: scale(<?= esc_attr($scale) ?>);
			}
			.back-to-top {
				width: 48px;
				position: fixed;
				bottom: 0px;
				<?= esc_html($location) ?>: 1rem;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 1rem;
				text-decoration: none;
				opacity: 0;
				pointer-events: none;
				transition: all 0.4s;
				z-index: 20;
			}
			.back-to-top.active-to-top {
				bottom: 16px;
				pointer-events: auto;
				opacity: 1;
			}
			html {
				scroll-behavior: smooth;
			}
			.back-to-top div:hover {
				animation: btt 1s linear infinite alternate;
			}
			@keyframes btt {
			0%   {transform: scale(1);}
			50%  {transform: scale(1.1);}
			100% {transform: scale(1);}
			}
			</style>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				const toTop = document.querySelector('.back-to-top');
				window.addEventListener("scroll", function() {
					if(window.pageYOffset > 100) {
						toTop.classList.add('active-to-top');
					} else {
						toTop.classList.remove('active-to-top');
					}
				});
				toTop.addEventListener('click', function(e) {
					e.preventDefault();
					window.scrollTo({top: 0, behavior: 'smooth'});
				});
			});
			</script>
		<?php
	}

}
