<?php
/**
 * Admin settings for configurable archive query rules.
 */
class Ap_Library_Archive_Settings {

	const OPTION_NAME = 'ap_library_archive_rules';

	/**
	 * Add submenu page under Library CPT.
	 */
	public function add_settings_submenu() {
		add_submenu_page(
			'edit.php?post_type=aplb_photo',
			__( 'Archive Settings', 'ap-library' ),
			__( 'Archive Settings', 'ap-library' ),
			'manage_options',
			'aplb-archive-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register option on admin init.
	 */
	public function register_settings() {
		if ( ! get_option( self::OPTION_NAME ) ) {
			add_option( self::OPTION_NAME, $this->get_default_rules() );
		}
	}

	/**
	 * Default archive rules.
	 */
	public function get_default_rules() {
		return [
			'tax:aplb_genre'           => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'tax:aplb_taken_date'      => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'tax:aplb_published_date'  => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'tax:aplb_keyword'         => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'post_type:aplb_photo'     => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'author'                   => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'date'                     => [ 'enabled' => true, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'search'                   => [ 'enabled' => false, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
			'front-page'               => [ 'enabled' => false, 'post_types' => [ 'aplb_photo' ], 'orderby' => 'meta_value', 'meta_key' => APLB_META_PUBLISHED_DATE, 'order' => 'DESC', 'posts_per_page' => '' ],
		];
	}

	/**
	 * Render settings page UI.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'ap-library' ) );
		}

		$rules   = get_option( self::OPTION_NAME, [] );
		$defaults = $this->get_default_rules();
		$all_post_types = [ 'aplb_photo' ];
		$contexts = [
			'tax:aplb_genre' => __( 'Photo Genre Taxonomy Archive', 'ap-library' ),
			'tax:aplb_taken_date' => __( 'Photo Taken Date Taxonomy Archive', 'ap-library' ),
			'tax:aplb_published_date' => __( 'Photo Published Date Taxonomy Archive', 'ap-library' ),
			'tax:aplb_keyword' => __( 'Photo Keyword Taxonomy Archive', 'ap-library' ),
			'post_type:aplb_photo' => __( 'Photo Post Type Archive', 'ap-library' ),
			'author'                 => __( 'Author Archives', 'ap-library' ),
			'date'                   => __( 'Date Archives (year/month/day)', 'ap-library' ),
			'search'                 => __( 'Search Results', 'ap-library' ),
			'front-page'             => __( 'Front Page (if shows posts)', 'ap-library' ),
		];

		// Handle reset to defaults
		if ( isset( $_POST['aplb_archive_reset_nonce'] ) && wp_verify_nonce( $_POST['aplb_archive_reset_nonce'], 'aplb_archive_reset' ) ) {
			update_option( self::OPTION_NAME, $this->get_default_rules() );
			$rules = $this->get_default_rules();
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Archive rules reset to defaults.', 'ap-library' ) . '</p></div>';
		}

		if ( isset( $_POST['aplb_archive_settings_nonce'] ) && wp_verify_nonce( $_POST['aplb_archive_settings_nonce'], 'aplb_archive_settings' ) ) {
			$sanitized = [];
			foreach ( $contexts as $key => $label ) {
				$enabled_key      = 'enabled_' . $key;
				$post_types_key   = 'post_types_' . $key;
				$orderby_key      = 'orderby_' . $key;
				$meta_key_key     = 'meta_key_' . $key;
				$order_key        = 'order_' . $key;
				$posts_per_page_key = 'posts_per_page_' . $key;

				$enabled = isset( $_POST[ $enabled_key ] ) && '1' === $_POST[ $enabled_key ];

				$incoming_pts = isset( $_POST[ $post_types_key ] ) && is_array( $_POST[ $post_types_key ] ) ? array_map( 'sanitize_text_field', $_POST[ $post_types_key ] ) : [];
				$valid_pts    = array_values( array_intersect( $incoming_pts, $all_post_types ) );
				if ( empty( $valid_pts ) ) { $valid_pts = $defaults[ $key ]['post_types']; }

				$orderby = isset( $_POST[ $orderby_key ] ) ? sanitize_text_field( $_POST[ $orderby_key ] ) : $defaults[ $key ]['orderby'];
				if ( ! in_array( $orderby, [ 'meta_value', 'date', 'title', 'menu_order' ], true ) ) { $orderby = 'meta_value'; }

				$meta_key = isset( $_POST[ $meta_key_key ] ) ? sanitize_text_field( $_POST[ $meta_key_key ] ) : '';
				if ( $orderby === 'meta_value' && $meta_key === '' ) { $meta_key = $defaults[ $key ]['meta_key']; }

				$order = isset( $_POST[ $order_key ] ) ? strtoupper( sanitize_text_field( $_POST[ $order_key ] ) ) : 'DESC';
				if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) { $order = 'DESC'; }

				$posts_per_page = isset( $_POST[ $posts_per_page_key ] ) ? sanitize_text_field( $_POST[ $posts_per_page_key ] ) : '';
				// Validate: must be empty, positive integer, or -1
				if ( $posts_per_page !== '' && $posts_per_page !== '-1' && ( ! is_numeric( $posts_per_page ) || (int) $posts_per_page < 1 ) ) {
					$posts_per_page = '';
				}

				$sanitized[ $key ] = [
					'enabled'        => $enabled,
					'post_types'     => $valid_pts,
					'orderby'        => $orderby,
					'meta_key'       => $meta_key,
					'order'          => $order,
					'posts_per_page' => $posts_per_page,
				];
			}
			update_option( self::OPTION_NAME, $sanitized );
			$rules = $sanitized;
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Archive rules updated.', 'ap-library' ) . '</p></div>';
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Archive Query Settings', 'ap-library' ); ?></h1>
			<p><?php esc_html_e( 'Configure which post types and ordering each archive context should use.', 'ap-library' ); ?></p>
			<p class="description"><?php esc_html_e( 'These rules are applied to the main query via pre_get_posts. Query Loop blocks using "Inherit query from URL" will reflect changes automatically. Leaving meta key empty (with meta_value orderby) falls back to default published date meta. Leave Posts Per Page empty to use WordPress default setting.', 'ap-library' ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( 'aplb_archive_settings', 'aplb_archive_settings_nonce' ); ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Enabled', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Archive Context', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Post Types', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Order By', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Meta Key (if meta_value)', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Order', 'ap-library' ); ?></th>
							<th><?php esc_html_e( 'Posts Per Page', 'ap-library' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $contexts as $key => $label ) :
						$current = isset( $rules[ $key ] ) ? $rules[ $key ] : $defaults[ $key ];
						$is_enabled = isset( $current['enabled'] ) ? (bool) $current['enabled'] : true; ?>
						<tr>
							<td style="text-align:center;">
								<input type="checkbox" name="enabled_<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $is_enabled ); ?> />
							</td>
							<td><strong><?php echo esc_html( $label ); ?></strong><br /><code><?php echo esc_html( $key ); ?></code></td>
							<td>
								<?php foreach ( $all_post_types as $pt ) : ?>
									<label style="display:block">
										<input type="checkbox" name="post_types_<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $pt ); ?>" <?php checked( in_array( $pt, $current['post_types'], true ) ); ?> />
										<?php echo esc_html( $pt ); ?>
									</label>
								<?php endforeach; ?>
							</td>
							<td>
									<select name="orderby_<?php echo esc_attr( $key ); ?>">
									<option value="meta_value" <?php selected( $current['orderby'], 'meta_value' ); ?>><?php esc_html_e( 'Meta Value', 'ap-library' ); ?></option>
									<option value="date" <?php selected( $current['orderby'], 'date' ); ?>><?php esc_html_e( 'Post Date', 'ap-library' ); ?></option>
									<option value="title" <?php selected( $current['orderby'], 'title' ); ?>><?php esc_html_e( 'Title', 'ap-library' ); ?></option>
									<option value="menu_order" <?php selected( $current['orderby'], 'menu_order' ); ?>><?php esc_html_e( 'Menu Order', 'ap-library' ); ?></option>
								</select>
							</td>
								<td><input type="text" name="meta_key_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $current['meta_key'] ); ?>" placeholder="<?php esc_attr_e( 'aplb_published_date', 'ap-library' ); ?>" /></td>
							<td>
									<select name="order_<?php echo esc_attr( $key ); ?>">
									<option value="DESC" <?php selected( $current['order'], 'DESC' ); ?>>DESC</option>
									<option value="ASC" <?php selected( $current['order'], 'ASC' ); ?>>ASC</option>
								</select>
							</td>
							<td>
								<input type="text" name="posts_per_page_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( isset( $current['posts_per_page'] ) ? $current['posts_per_page'] : '' ); ?>" placeholder="<?php esc_attr_e( 'WP default', 'ap-library' ); ?>" style="width:80px;" />
								<p class="description" style="margin:0;"><?php esc_html_e( 'Empty = WP default, -1 = all', 'ap-library' ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'Save Archive Rules', 'ap-library' ) ); ?>
			</form>

			<hr style="margin: 2em 0;" />

			<h2><?php esc_html_e( 'Reset Settings', 'ap-library' ); ?></h2>
			<p><?php esc_html_e( 'Reset all archive rules to plugin defaults. This action cannot be undone.', 'ap-library' ); ?></p>
			<form method="post" action="" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all archive rules to defaults? This cannot be undone.', 'ap-library' ) ); ?>');">
				<?php wp_nonce_field( 'aplb_archive_reset', 'aplb_archive_reset_nonce' ); ?>
				<?php submit_button( __( 'Reset to Defaults', 'ap-library' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}
}
