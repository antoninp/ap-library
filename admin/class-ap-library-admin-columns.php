<?php

/**
 * Admin Columns functionality for the plugin
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin
 */

class Ap_Library_Admin_Columns {

    /**
    * Add thumbnail and date columns to the aplb_photo post type list table.
     *
     * @since    1.0.0
     * @param    array    $columns    The existing columns.
     * @return   array                The modified columns.
     */
    public function add_aplb_photo_thumbnail_column( $columns ) {
        // Define the desired column order
        $ordered = array();
        
        // Add columns in the desired order
        if ( isset( $columns['cb'] ) ) {
            $ordered['cb'] = $columns['cb'];
        }
        
        // Thumbnail
        $ordered['thumbnail'] = __( 'Thumbnail', 'ap-library' );
        
        // Title
        if ( isset( $columns['title'] ) ) {
            $ordered['title'] = $columns['title'];
        }
        
        // Photo Genre (taxonomy)
        if ( isset( $columns['taxonomy-aplb_genre'] ) ) {
            $ordered['taxonomy-aplb_genre'] = $columns['taxonomy-aplb_genre'];
        }
        
        // Portfolios (taxonomy)
        if ( isset( $columns['taxonomy-aplb_portfolio'] ) ) {
            $ordered['taxonomy-aplb_portfolio'] = $columns['taxonomy-aplb_portfolio'];
        }
        
        // Photo Keywords (taxonomy)
        if ( isset( $columns['taxonomy-aplb_keyword'] ) ) {
            $ordered['taxonomy-aplb_keyword'] = $columns['taxonomy-aplb_keyword'];
        }

        // Author
        if ( isset( $columns['author'] ) ) {
            $ordered['author'] = $columns['author'];
        }
        
        // Taken Date (taxonomy)
        if ( isset( $columns['taxonomy-aplb_taken_date'] ) ) {
            $ordered['taxonomy-aplb_taken_date'] = $columns['taxonomy-aplb_taken_date'];
        }

        // Photo Taken (meta)
        $ordered['aplb_taken_date'] = __( 'Photo Taken', 'ap-library' );
        
        // Published Date (taxonomy)
        if ( isset( $columns['taxonomy-aplb_published_date'] ) ) {
            $ordered['taxonomy-aplb_published_date'] = $columns['taxonomy-aplb_published_date'];
        }
        
        // Photo Published (meta)
        $ordered['aplb_published_date'] = __( 'Photo Published', 'ap-library' );

        // Date (WordPress post date)
        if ( isset( $columns['date'] ) ) {
            $ordered['date'] = $columns['date'];
        }

        // Add any remaining columns that weren't explicitly ordered
        foreach ( $columns as $key => $value ) {
            if ( ! isset( $ordered[ $key ] ) ) {
                $ordered[ $key ] = $value;
            }
        }
        
        return $ordered;
    }

    /**
     * Render the thumbnail and date column content.
     *
     * @since    1.0.0
     * @param    string    $column    The name of the column being rendered.
     * @param    int       $post_id   The ID of the current post.
     */
    public function render_aplb_photo_thumbnail_column( $column, $post_id ) {
        if ( $column === 'thumbnail' ) {
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
            } else {
                echo '&mdash;';
            }
        } elseif ( $column === 'aplb_published_date' ) {
            $date = get_post_meta( $post_id, APLB_META_PUBLISHED_DATE, true );
            if ( $date ) {
                $timestamp = strtotime( $date );
                $format = get_option( 'ap_library_date_format', 'M j, Y' );
                echo '<span data-date="' . esc_attr( $date ) . '">';
                echo $timestamp ? esc_html( date_i18n( $format, $timestamp ) ) : esc_html( $date );
                echo '</span>';
            } else {
                echo '&mdash;';
            }
        } elseif ( $column === 'aplb_taken_date' ) {
            $date = get_post_meta( $post_id, APLB_META_TAKEN_DATE, true );
            if ( $date ) {
                $timestamp = strtotime( $date );
                $format = get_option( 'ap_library_date_format', 'M j, Y' );
                echo '<span data-date="' . esc_attr( $date ) . '">';
                echo $timestamp ? esc_html( date_i18n( $format, $timestamp ) ) : esc_html( $date );
                echo '</span>';
            } else {
                echo '&mdash;';
            }
        }
    }

    /**
     * Make date columns sortable.
     *
     * @since    1.0.0
     * @param    array    $columns    The existing sortable columns.
     * @return   array                The modified sortable columns.
     */
    public function make_date_columns_sortable( $columns ) {
        $columns['aplb_published_date'] = 'aplb_published_date';
        $columns['aplb_taken_date'] = 'aplb_taken_date';
        return $columns;
    }

    /**
     * Handle custom column sorting.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The WP_Query instance.
     */
    public function handle_date_column_sorting( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( 'aplb_published_date' === $orderby ) {
            $query->set( 'meta_key', APLB_META_PUBLISHED_DATE );
            $query->set( 'orderby', 'meta_value' );
        } elseif ( 'aplb_taken_date' === $orderby ) {
            $query->set( 'meta_key', APLB_META_TAKEN_DATE );
            $query->set( 'orderby', 'meta_value' );
        }
    }

    /**
     * Add quick edit fields for date columns.
     *
     * @since    1.0.0
     * @param    string    $column_name    The name of the column.
     * @param    string    $post_type      The post type.
     */
    public function add_quick_edit_date_fields( $column_name, $post_type ) {
        if ( 'aplb_photo' !== $post_type ) {
            return;
        }

        // Only render once for the first date column to avoid duplicates
        if ( 'aplb_published_date' === $column_name ) {
            ?>
            <fieldset class="inline-edit-col-right">
                <div class="inline-edit-col">
                    <label>
                        <span class="title"><?php esc_html_e( 'Photo Published', 'ap-library' ); ?></span>
                        <span class="input-text-wrap">
                            <input type="date" name="aplb_published_date" class="aplb-published-date-quick-edit" value="" />
                        </span>
                    </label>
                    <label>
                        <span class="title"><?php esc_html_e( 'Photo Taken', 'ap-library' ); ?></span>
                        <span class="input-text-wrap">
                            <input type="date" name="aplb_taken_date" class="aplb-taken-date-quick-edit" value="" />
                        </span>
                    </label>
                </div>
            </fieldset>
            <?php
        }
    }

    /**
     * Save quick edit data.
     *
     * @since    1.0.0
     * @param    int    $post_id    Post ID.
     */
    public function save_quick_edit_data( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'aplb_photo' ) {
            return;
        }

        // Save published date
        if ( isset( $_POST['aplb_published_date'] ) ) {
            $published_date = sanitize_text_field( $_POST['aplb_published_date'] );
            if ( $published_date ) {
                update_post_meta( $post_id, APLB_META_PUBLISHED_DATE, $published_date );
                $this->sync_date_to_taxonomy( $post_id, $published_date, 'aplb_published_date' );
            }
        }

        // Save taken date
        if ( isset( $_POST['aplb_taken_date'] ) ) {
            $taken_date = sanitize_text_field( $_POST['aplb_taken_date'] );
            if ( $taken_date ) {
                update_post_meta( $post_id, APLB_META_TAKEN_DATE, $taken_date );
                $this->sync_date_to_taxonomy( $post_id, $taken_date, 'aplb_taken_date' );
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
        $month_name = date_i18n( 'F Y', $timestamp );
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
     * Enqueue admin JavaScript for quick edit.
     *
     * @since    1.0.0
     */
    public function enqueue_quick_edit_script() {
        global $pagenow, $typenow;
        
        if ( 'edit.php' === $pagenow && 'aplb_photo' === $typenow ) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Populate quick edit fields with existing values
                var $wp_inline_edit = inlineEditPost.edit;
                inlineEditPost.edit = function(id) {
                    $wp_inline_edit.apply(this, arguments);
                    
                    var post_id = 0;
                    if (typeof(id) == 'object') {
                        post_id = parseInt(this.getId(id));
                    }
                    
                    if (post_id > 0) {
                        var $row = $('#post-' + post_id);
                        
                        // Get dates from data attributes
                        var published_date = $row.find('.column-aplb_published_date span').data('date');
                        var taken_date = $row.find('.column-aplb_taken_date span').data('date');
                        
                        // Set values in quick edit fields
                        if (published_date) {
                            $('.aplb-published-date-quick-edit').val(published_date);
                        }
                        if (taken_date) {
                            $('.aplb-taken-date-quick-edit').val(taken_date);
                        }
                    }
                };
            });
            </script>
            <?php
        }
    }
}