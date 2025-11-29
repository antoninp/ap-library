<?php
/**
 * Portfolio-specific admin functionality.
 *
 * Handles term meta for portfolio featured images and custom ordering.
 *
 * @since      1.3.1
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
 * Portfolio admin features class.
 *
 * @since      1.3.1
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo
 */
class Ap_Library_Portfolio {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.1
	 */
	public function __construct() {
	}

	/**
	 * Add custom fields to portfolio taxonomy edit form.
	 *
	 * @since    1.3.1
	 * @param    WP_Term    $term    Current taxonomy term object.
	 */
	public function add_portfolio_term_fields( $term ) {
		$term_id = $term->term_id;
		$featured_image_id = get_term_meta( $term_id, 'aplb_portfolio_featured_image', true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="aplb_portfolio_featured_image"><?php esc_html_e( 'Portfolio Cover Image', 'ap-library' ); ?></label>
			</th>
			<td>
				<div class="aplb-portfolio-image-wrapper">
					<input type="hidden" id="aplb_portfolio_featured_image" name="aplb_portfolio_featured_image" value="<?php echo esc_attr( $featured_image_id ); ?>" />
					<div class="aplb-portfolio-image-preview">
						<?php if ( $featured_image_id ) : ?>
							<?php echo wp_get_attachment_image( $featured_image_id, 'medium' ); ?>
						<?php endif; ?>
					</div>
					<p>
						<button type="button" class="button aplb-upload-image-button">
							<?php echo $featured_image_id ? esc_html__( 'Change Cover Image', 'ap-library' ) : esc_html__( 'Set Cover Image', 'ap-library' ); ?>
						</button>
						<?php if ( $featured_image_id ) : ?>
							<button type="button" class="button aplb-remove-image-button"><?php esc_html_e( 'Remove Cover Image', 'ap-library' ); ?></button>
						<?php endif; ?>
					</p>
				</div>
				<p class="description"><?php esc_html_e( 'Select a cover image for this portfolio. This can be displayed on portfolio listing pages.', 'ap-library' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add custom fields to portfolio taxonomy add form.
	 *
	 * @since    1.3.1
	 */
	public function add_portfolio_term_fields_new() {
		?>
		<div class="form-field">
			<label for="aplb_portfolio_featured_image"><?php esc_html_e( 'Portfolio Cover Image', 'ap-library' ); ?></label>
			<div class="aplb-portfolio-image-wrapper">
				<input type="hidden" id="aplb_portfolio_featured_image" name="aplb_portfolio_featured_image" value="" />
				<div class="aplb-portfolio-image-preview"></div>
				<p>
					<button type="button" class="button aplb-upload-image-button"><?php esc_html_e( 'Set Cover Image', 'ap-library' ); ?></button>
				</p>
			</div>
			<p class="description"><?php esc_html_e( 'Select a cover image for this portfolio. This can be displayed on portfolio listing pages.', 'ap-library' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Save portfolio term meta.
	 *
	 * @since    1.3.1
	 * @param    int    $term_id    Term ID.
	 */
	public function save_portfolio_term_meta( $term_id ) {
		if ( isset( $_POST['aplb_portfolio_featured_image'] ) ) {
			$image_id = absint( $_POST['aplb_portfolio_featured_image'] );
			if ( $image_id ) {
				update_term_meta( $term_id, 'aplb_portfolio_featured_image', $image_id );
			} else {
				delete_term_meta( $term_id, 'aplb_portfolio_featured_image' );
			}
		}
	}

	/**
	 * Enqueue admin scripts for portfolio term meta.
	 *
	 * @since    1.3.1
	 * @param    string    $hook    Current admin page hook.
	 */
	public function enqueue_portfolio_admin_scripts( $hook ) {
		// Only load on taxonomy edit pages
		if ( 'edit-tags.php' !== $hook && 'term.php' !== $hook ) {
			return;
		}

		// Check if we're editing the portfolio taxonomy
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== 'aplb_portfolio' ) {
			return;
		}

		// Enqueue WordPress media library
		wp_enqueue_media();

		// Enqueue custom script
		wp_enqueue_script(
			'aplb-portfolio-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/ap-library-portfolio.js',
			array( 'jquery' ),
			AP_LIBRARY_VERSION,
			true
		);

		wp_enqueue_style(
			'aplb-portfolio-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'admin/css/ap-library-portfolio.css',
			array(),
			AP_LIBRARY_VERSION
		);
	}
}
