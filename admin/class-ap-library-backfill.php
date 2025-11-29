<?php

/**
 * Backfill tool for existing photo posts.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 * @modified   1.3.1 Date term creation now uses global date format where applicable.
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
			'edit.php?post_type=aplb_photo',
			__( 'Backfill', 'ap-library' ),
			__( 'Backfill', 'ap-library' ),
			'manage_options',
			'aplb-backfill',
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
			<h1><?php esc_html_e( 'Backfill Photo Metadata', 'ap-library' ); ?></h1>
			<p><?php esc_html_e( 'Use these tools to populate or regenerate date and keyword taxonomies from media metadata.', 'ap-library' ); ?></p>
			<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Warning:', 'ap-library' ); ?></strong> <?php esc_html_e( 'Backfill operations with "overwrite existing" enabled will replace current data. These changes are permanent and cannot be undone automatically. Consider backing up your database before running overwrite operations.', 'ap-library' ); ?></p></div>
			<?php
			// Handle submissions independently.
			if ( isset( $_POST['aplb_backfill_taken_nonce'] ) && wp_verify_nonce( $_POST['aplb_backfill_taken_nonce'], 'aplb_backfill_taken' ) ) {
				$this->process_taken_date_backfill();
			}
			if ( isset( $_POST['aplb_backfill_published_nonce'] ) && wp_verify_nonce( $_POST['aplb_backfill_published_nonce'], 'aplb_backfill_published' ) ) {
				$this->process_published_date_backfill();
			}
			if ( isset( $_POST['aplb_backfill_keywords_nonce'] ) && wp_verify_nonce( $_POST['aplb_backfill_keywords_nonce'], 'aplb_backfill_keywords' ) ) {
				$this->process_keywords_backfill();
			}
			?>

			<h2><?php esc_html_e( 'Taken Date Backfill', 'ap-library' ); ?></h2>
			<p><?php esc_html_e( 'Extract EXIF taken dates from featured images and synchronize to hierarchical taxonomy (Year → Month → Day).', 'ap-library' ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( 'aplb_backfill_taken', 'aplb_backfill_taken_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Options', 'ap-library' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="overwrite_existing_taken" value="1" />
								<?php esc_html_e( 'Overwrite existing taken dates', 'ap-library' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If unchecked, only posts without taken dates will be processed.', 'ap-library' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Run Taken Date Backfill', 'ap-library' ), 'primary', 'submit', false ); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Published Date Backfill', 'ap-library' ); ?></h2>
			<p><?php esc_html_e( 'Ensure published date meta is set (defaults to post creation date) and synchronized to flat taxonomy.', 'ap-library' ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( 'aplb_backfill_published', 'aplb_backfill_published_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Options', 'ap-library' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="overwrite_existing_published" value="1" />
								<?php esc_html_e( 'Overwrite existing published dates', 'ap-library' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If unchecked, only posts without published dates will be processed.', 'ap-library' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Run Published Date Backfill', 'ap-library' ), 'primary', 'submit', false ); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Keywords Backfill', 'ap-library' ); ?></h2>
			<p><?php esc_html_e( 'Extract IPTC keywords from featured images and populate the keyword taxonomy.', 'ap-library' ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( 'aplb_backfill_keywords', 'aplb_backfill_keywords_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Options', 'ap-library' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="overwrite_existing_keywords" value="1" />
								<?php esc_html_e( 'Overwrite existing keyword terms', 'ap-library' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If unchecked, only posts without keywords will be processed.', 'ap-library' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Run Keywords Backfill', 'ap-library' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Process taken date backfill.
	 *
	 * @since 1.2.0
	 */
	private function process_taken_date_backfill() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-exif.php';

		$overwrite = isset( $_POST['overwrite_existing_taken'] ) && '1' === $_POST['overwrite_existing_taken'];
		
		$args = array(
			'post_type'      => 'aplb_photo',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);

		$photos = get_posts( $args );
		$processed = 0;
		$updated = 0;

		foreach ( $photos as $post ) {
			$post_id = $post->ID;
			$processed++;

			$existing_taken = get_post_meta( $post_id, APLB_META_TAKEN_DATE, true );
			if ( $overwrite || ! $existing_taken ) {
				$taken_date = Ap_Library_EXIF::get_taken_date_from_post( $post_id );
				if ( $taken_date ) {
					update_post_meta( $post_id, APLB_META_TAKEN_DATE, $taken_date );
					$this->sync_date_to_taxonomy( $post_id, $taken_date, 'aplb_taken_date' );
					$updated++;
				}
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(
			esc_html__( 'Taken date backfill complete! Processed %d posts, updated %d taken dates.', 'ap-library' ),
			$processed,
			$updated
		) . '</p></div>';
	}

	/**
	 * Process published date backfill.
	 *
	 * @since 1.2.0
	 */
	private function process_published_date_backfill() {
		$overwrite = isset( $_POST['overwrite_existing_published'] ) && '1' === $_POST['overwrite_existing_published'];
		
		$args = array(
			'post_type'      => 'aplb_photo',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);

		$photos = get_posts( $args );
		$processed = 0;
		$updated = 0;

		foreach ( $photos as $post ) {
			$post_id = $post->ID;
			$processed++;

			$existing_published = get_post_meta( $post_id, APLB_META_PUBLISHED_DATE, true );
			if ( $overwrite || ! $existing_published ) {
				$published_date = gmdate( 'Y-m-d', strtotime( $post->post_date ) );
				update_post_meta( $post_id, APLB_META_PUBLISHED_DATE, $published_date );
				$this->sync_date_to_taxonomy( $post_id, $published_date, 'aplb_published_date' );
				$updated++;
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(
			esc_html__( 'Published date backfill complete! Processed %d posts, updated %d published dates.', 'ap-library' ),
			$processed,
			$updated
		) . '</p></div>';
	}

	/**
	 * Process keywords backfill from IPTC metadata.
	 *
	 * @since 1.2.0
	 */
	private function process_keywords_backfill() {
		if ( ! taxonomy_exists( 'aplb_keyword' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Keyword taxonomy not registered.', 'ap-library' ) . '</p></div>';
			return;
		}
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ap-library-exif.php';
		$overwrite = isset( $_POST['overwrite_existing_keywords'] ) && '1' === $_POST['overwrite_existing_keywords'];
		$args = array(
			'post_type'      => 'aplb_photo',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);
		$photos = get_posts( $args );
		$processed = 0;
		$updated   = 0;
		foreach ( $photos as $post ) {
			$post_id = $post->ID;
			$processed++;
			$existing_kw = wp_get_object_terms( $post_id, 'aplb_keyword', array( 'fields' => 'ids' ) );
			if ( ! $overwrite && ! empty( $existing_kw ) ) {
				continue;
			}
			$keywords = Ap_Library_EXIF::get_keywords_from_post( $post_id );
			if ( empty( $keywords ) ) {
				continue;
			}
			$term_ids = array();
			foreach ( $keywords as $kw ) {
				$kw = sanitize_text_field( $kw );
				if ( $kw === '' ) { continue; }
				// Normalize slug for case-insensitive matching
				$slug = sanitize_title( strtolower( $kw ) );
				// Check if term already exists by slug
				$existing = get_term_by( 'slug', $slug, 'aplb_keyword' );
				if ( ! $existing ) {
					// Create new term with title-cased name derived from slug
					$name = $this->format_keyword_name( $slug );
					$created = wp_insert_term( $name, 'aplb_keyword', array( 'slug' => $slug ) );
					if ( ! is_wp_error( $created ) ) {
						$term_ids[] = (int) $created['term_id'];
					}
				} else {
					// Use existing term
					$term_ids[] = (int) $existing->term_id;
				}
			}
			if ( ! empty( $term_ids ) ) {
				wp_set_object_terms( $post_id, $term_ids, 'aplb_keyword', false );
				$updated++;
			}
		}
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo sprintf(
			esc_html__( 'Keywords backfill complete! Processed %d posts, updated %d keyword sets.', 'ap-library' ),
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

		// For aplb_taken_date, create hierarchical structure: Year -> Month -> Day
		if ( $taxonomy === 'aplb_taken_date' ) {
			$term_id = $this->sync_hierarchical_date( $date, $taxonomy );
		} else {
			// For aplb_published_date, keep flat structure
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
	private function sync_hierarchical_date( $date, $taxonomy ) {
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
		$month_name = date_i18n( 'F Y', $timestamp ); // e.g., "May 2023"
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

	/**
	 * Format keyword for display with title case.
	 *
	 * @since 1.2.0
	 * @param string $keyword Normalized keyword slug.
	 * @return string Title-cased keyword for display.
	 */
	private function format_keyword_name( $keyword ) {
		// Replace hyphens/underscores with spaces and title case
		$keyword = str_replace( array( '-', '_' ), ' ', $keyword );
		return ucwords( $keyword );
	}
}
