<?php

/**
 * Meta box functionality for date fields.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 * @modified   1.3.1 Date taxonomy sync respects global date format setting.
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
* Handles meta boxes for photo post type.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo
 */
class Ap_Library_Meta_Box {

	/**
	 * Register meta boxes.
	 *
	 * @since    1.0.0
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'aplb_dates_meta_box',
			__( 'Photo Dates', 'ap-library' ),
			array( $this, 'render_dates_meta_box' ),
			'aplb_photo',
			'side',
			'high'
		);
	}

	/**
	 * Render the dates meta box.
	 *
	 * @since    1.0.0
	 * @param    WP_Post    $post    Current post object.
	 */
	public function render_dates_meta_box( $post ) {
		wp_nonce_field( 'aplb_dates_meta_box', 'aplb_dates_meta_box_nonce' );

		$published_date = get_post_meta( $post->ID, APLB_META_PUBLISHED_DATE, true );
		$taken_date     = get_post_meta( $post->ID, APLB_META_TAKEN_DATE, true );

		?>
		<div class="aplb-dates-meta-box">
			<p>
				<label for="aplb_published_date"><strong><?php esc_html_e( 'Photo Published:', 'ap-library' ); ?></strong></label>
				<input 
					type="date" 
					id="aplb_published_date" 
					name="aplb_published_date" 
					value="<?php echo esc_attr( $published_date ); ?>"
					style="width: 100%;"
				/>
				<em style="font-size: 11px; display: block; margin-top: 4px;">
					<?php esc_html_e( 'Date when photo was published/shared', 'ap-library' ); ?>
				</em>
			</p>

			<p>
				<label for="aplb_taken_date"><strong><?php esc_html_e( 'Photo Taken:', 'ap-library' ); ?></strong></label>
				<input 
					type="date" 
					id="aplb_taken_date" 
					name="aplb_taken_date" 
					value="<?php echo esc_attr( $taken_date ); ?>"
					style="width: 100%;"
				/>
				<em style="font-size: 11px; display: block; margin-top: 4px;">
					<?php esc_html_e( 'Date from EXIF data (when photo was taken)', 'ap-library' ); ?>
				</em>
			</p>

			<?php if ( has_post_thumbnail( $post->ID ) && ! $taken_date ) : ?>
			<p>
				<button type="button" id="aplb_extract_exif" class="button button-secondary" style="width: 100%;">
					<?php esc_html_e( 'Extract from EXIF', 'ap-library' ); ?>
				</button>
			</p>
			<script>
			jQuery(document).ready(function($) {
				$('#aplb_extract_exif').on('click', function(e) {
					e.preventDefault();
					var btn = $(this);
					btn.prop('disabled', true).text('<?php esc_html_e( 'Extracting...', 'ap-library' ); ?>');
					
					$.post(ajaxurl, {
						action: 'aplb_extract_exif',
						post_id: <?php echo intval( $post->ID ); ?>,
						nonce: '<?php echo wp_create_nonce( 'aplb_extract_exif' ); ?>'
					}, function(response) {
						if (response.success && response.data.taken_date) {
							$('#aplb_taken_date').val(response.data.taken_date);
							btn.text('<?php esc_html_e( 'Extracted!', 'ap-library' ); ?>');
						} else {
							alert('<?php esc_html_e( 'No EXIF date found', 'ap-library' ); ?>');
							btn.prop('disabled', false).text('<?php esc_html_e( 'Extract from EXIF', 'ap-library' ); ?>');
						}
					});
				});
			});
			</script>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    Post ID.
	 */
	public function save_meta_box( $post_id ) {
		// Security checks
		if ( ! isset( $_POST['aplb_dates_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['aplb_dates_meta_box_nonce'], 'aplb_dates_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save published date
		if ( isset( $_POST['aplb_published_date'] ) ) {
			$published_date = sanitize_text_field( $_POST['aplb_published_date'] );
			if ( $published_date ) {
				update_post_meta( $post_id, APLB_META_PUBLISHED_DATE, $published_date );
				$this->sync_date_to_taxonomy( $post_id, $published_date, 'aplb_published_date' );
			} else {
				delete_post_meta( $post_id, APLB_META_PUBLISHED_DATE );
				wp_set_object_terms( $post_id, array(), 'aplb_published_date' );
			}
		}

		// Save taken date
		if ( isset( $_POST['aplb_taken_date'] ) ) {
			$taken_date = sanitize_text_field( $_POST['aplb_taken_date'] );
			if ( $taken_date ) {
				update_post_meta( $post_id, APLB_META_TAKEN_DATE, $taken_date );
				$this->sync_date_to_taxonomy( $post_id, $taken_date, 'aplb_taken_date' );
			} else {
				delete_post_meta( $post_id, APLB_META_TAKEN_DATE );
				wp_set_object_terms( $post_id, array(), 'aplb_taken_date' );
			}
		}
	}

	/**
	 * Sync date meta to shadow taxonomy.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    Post ID.
	 * @param    string    $date       Date in YYYY-MM-DD format.
	 * @param    string    $taxonomy   Taxonomy name.
	 */
	public function sync_date_to_taxonomy( $post_id, $date, $taxonomy ) {
		if ( ! $date || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		// For aplb_taken_date, create hierarchical structure: Year -> Month -> Day
		if ( $taxonomy === 'aplb_taken_date' ) {
			$term_id = $this->sync_hierarchical_date( $date, $taxonomy );
		} else {
			// For aplb_published_date, keep flat structure
			$term_id = $this->sync_flat_date( $date, $taxonomy );
		}

		if ( $term_id ) {
			// Set the term for this post (use the day-level term)
			wp_set_object_terms( $post_id, array( $term_id ), $taxonomy, false );
		}
	}

	/**
	 * Sync date to flat taxonomy (original behavior).
	 *
	 * @since    1.0.0
	 * @param    string    $date       Date in YYYY-MM-DD format.
	 * @param    string    $taxonomy   Taxonomy name.
	 * @return   int|null              Term ID or null on error.
	 */
	public function sync_flat_date( $date, $taxonomy ) {
		$term = get_term_by( 'slug', $date, $taxonomy );
		
		if ( ! $term ) {
			$timestamp = strtotime( $date );
			$format = get_option( 'ap_library_date_format', 'M j, Y' );
			$term_name = $timestamp ? date_i18n( $format, $timestamp ) : $date;
			
			$result = wp_insert_term( $term_name, $taxonomy, array( 'slug' => $date ) );
			
			if ( is_wp_error( $result ) ) {
				return null;
			}
			
			return $result['term_id'];
		}
		
		return $term->term_id;
	}

	/**
	 * Sync date to hierarchical taxonomy: Year -> Month -> Day.
	 *
	 * @since    1.0.0
	 * @param    string    $date       Date in YYYY-MM-DD format.
	 * @param    string    $taxonomy   Taxonomy name.
	 * @return   int|null              Day term ID or null on error.
	 */
	public function sync_hierarchical_date( $date, $taxonomy ) {
		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return null;
		}

		// Parse date components
		$year  = date( 'Y', $timestamp );
		$month = date( 'm', $timestamp );
		$day   = date( 'd', $timestamp );
		
		$format = get_option( 'ap_library_date_format', 'M j, Y' );
		$year_name  = $year;
		$month_name = date_i18n( 'F', $timestamp ); // "November"
		$day_name   = date_i18n( $format, $timestamp );

		// Create/get year term
		$year_term = get_term_by( 'slug', $year, $taxonomy );
		if ( ! $year_term ) {
			$year_result = wp_insert_term( $year_name, $taxonomy, array( 'slug' => $year ) );
			if ( is_wp_error( $year_result ) ) {
				return null;
			}
			$year_term_id = $year_result['term_id'];
		} else {
			$year_term_id = $year_term->term_id;
		}

		// Create/get month term as child of year
		$month_slug = $year . '-' . $month; // "2023-11"
		$month_term = get_term_by( 'slug', $month_slug, $taxonomy );
		if ( ! $month_term ) {
			$month_result = wp_insert_term( $month_name, $taxonomy, array(
				'slug'   => $month_slug,
				'parent' => $year_term_id,
			) );
			if ( is_wp_error( $month_result ) ) {
				return null;
			}
			$month_term_id = $month_result['term_id'];
		} else {
			$month_term_id = $month_term->term_id;
		}

		// Create/get day term as child of month
		$day_slug = $date; // "2023-11-15"
		$day_term = get_term_by( 'slug', $day_slug, $taxonomy );
		if ( ! $day_term ) {
			$day_result = wp_insert_term( $day_name, $taxonomy, array(
				'slug'   => $day_slug,
				'parent' => $month_term_id,
			) );
			if ( is_wp_error( $day_result ) ) {
				return null;
			}
			return $day_result['term_id'];
		}
		
		return $day_term->term_id;
	}

	/**
	 * AJAX handler to extract EXIF data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_extract_exif() {
		check_ajax_referer( 'aplb_extract_exif', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error();
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-exif.php';
		
		$taken_date = Ap_Library_EXIF::get_taken_date_from_post( $post_id );

		if ( $taken_date ) {
			wp_send_json_success( array( 'taken_date' => $taken_date ) );
		} else {
			wp_send_json_error();
		}
	}
}
