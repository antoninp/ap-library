<?php

/**
 * Backfill tool for existing uploads.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

/**
 * Handles backfilling date meta and taxonomy sync for existing posts.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 * @author     Antonin Puleo
 */
class Ap_Library_Backfill {

	/**
	 * Add admin menu item for backfill tool.
	 *
	 * @since    1.0.0
	 */
	public function add_backfill_submenu() {
		add_submenu_page(
			'edit.php?post_type=aplb_uploads',
			__( 'Backfill Dates', 'ap-library' ),
			__( 'Backfill Dates', 'ap-library' ),
			'manage_options',
			'aplb-backfill-dates',
			array( $this, 'render_backfill_page' )
		);
	}

	/**
	 * Render the backfill tool page.
	 *
	 * @since    1.0.0
	 */
	public function render_backfill_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ap-library' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Backfill Photo Dates', 'ap-library' ); ?></h1>
			<p><?php esc_html_e( 'This tool will scan all existing uploads and extract EXIF dates, populate published dates (using post_date as fallback), and sync to date taxonomies.', 'ap-library' ); ?></p>
			
			<?php
			// Handle form submission
			if ( isset( $_POST['aplb_backfill_nonce'] ) && wp_verify_nonce( $_POST['aplb_backfill_nonce'], 'aplb_backfill_dates' ) ) {
				$this->process_backfill();
			}
			?>

			<form method="post" action="">
				<?php wp_nonce_field( 'aplb_backfill_dates', 'aplb_backfill_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Options', 'ap-library' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="overwrite_existing" value="1" />
								<?php esc_html_e( 'Overwrite existing date values', 'ap-library' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If unchecked, only empty dates will be filled.', 'ap-library' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Start Backfill', 'ap-library' ), 'primary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Process the backfill operation.
	 *
	 * @since    1.0.0
	 */
	private function process_backfill() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-exif.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ap-library-meta-box.php';

		$overwrite = isset( $_POST['overwrite_existing'] ) && '1' === $_POST['overwrite_existing'];
		
		$args = array(
			'post_type'      => 'aplb_uploads',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);

		$uploads = get_posts( $args );
		$processed = 0;
		$updated = 0;

		foreach ( $uploads as $post ) {
			$post_id = $post->ID;
			$processed++;

			// Process taken date
			$existing_taken = get_post_meta( $post_id, APLB_META_TAKEN_DATE, true );
			if ( $overwrite || ! $existing_taken ) {
				$taken_date = Ap_Library_EXIF::get_taken_date_from_post( $post_id );
				if ( $taken_date ) {
					update_post_meta( $post_id, APLB_META_TAKEN_DATE, $taken_date );
					$this->sync_date_to_taxonomy( $post_id, $taken_date, 'aplb_uploads_tdate' );
					$updated++;
				}
			}

			// Process published date (use post_date as fallback)
			$existing_published = get_post_meta( $post_id, APLB_META_PUBLISHED_DATE, true );
			if ( $overwrite || ! $existing_published ) {
				// Default to post creation date
				$published_date = gmdate( 'Y-m-d', strtotime( $post->post_date ) );
				update_post_meta( $post_id, APLB_META_PUBLISHED_DATE, $published_date );
				$this->sync_date_to_taxonomy( $post_id, $published_date, 'aplb_library_pdate' );
				$updated++;
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>';
		echo sprintf(
			esc_html__( 'Backfill complete! Processed %d posts, updated %d dates.', 'ap-library' ),
			$processed,
			$updated
		);
		echo '</p></div>';
	}

	/**
	 * Sync date meta to shadow taxonomy.
	 *
	 * @since    1.0.0
	 * @param    int       $post_id    Post ID.
	 * @param    string    $date       Date in YYYY-MM-DD format.
	 * @param    string    $taxonomy   Taxonomy name.
	 */
	private function sync_date_to_taxonomy( $post_id, $date, $taxonomy ) {
		if ( ! $date || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		// For aplb_uploads_tdate, create hierarchical structure: Year -> Month -> Day
		if ( $taxonomy === 'aplb_uploads_tdate' ) {
			$term_id = $this->sync_hierarchical_date( $date, $taxonomy );
		} else {
			// For aplb_library_pdate, keep flat structure
			$term_id = $this->sync_flat_date( $date, $taxonomy );
		}

		if ( $term_id ) {
			wp_set_object_terms( $post_id, array( $term_id ), $taxonomy, false );
		}
	}

	/**
	 * Sync date to flat taxonomy.
	 *
	 * @since    1.0.0
	 * @param    string    $date       Date in YYYY-MM-DD format.
	 * @param    string    $taxonomy   Taxonomy name.
	 * @return   int|null              Term ID or null on error.
	 */
	private function sync_flat_date( $date, $taxonomy ) {
		$term = get_term_by( 'slug', $date, $taxonomy );
		
		if ( ! $term ) {
			$timestamp = strtotime( $date );
			$term_name = $timestamp ? date_i18n( 'F j, Y', $timestamp ) : $date;
			
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
	private function sync_hierarchical_date( $date, $taxonomy ) {
		$timestamp = strtotime( $date );
		if ( ! $timestamp ) {
			return null;
		}

		// Parse date components
		$year  = date( 'Y', $timestamp );
		$month = date( 'm', $timestamp );
		$day   = date( 'd', $timestamp );
		
		$year_name  = $year;
		$month_name = date_i18n( 'F', $timestamp );
		$day_name   = date_i18n( 'j', $timestamp );

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
		$month_slug = $year . '-' . $month;
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
		$day_slug = $date;
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
}
