<?php
require_once plugin_dir_path( __FILE__ ) . '../helpers/PhotoDateHelper.php';
require_once plugin_dir_path( __FILE__ ) . '../helpers/PhotoTermHelper.php';

/**
 * Class responsible for creating photo posts when images are uploaded.
 */
class PhotoPostCreator {

    /**
     * Create a new 'aplb_photo' post when an image is uploaded.
     *
     * @param int  $image_id The ID of the uploaded image.
     * @param bool $force    Whether to force post creation regardless of settings.
     */
    public function create_post_on_image_upload( $image_id, $force = false ) {
        if ( ! $force && ! get_option( 'ap_library_auto_create_post_on_upload', false ) ) return;
        if ( ! wp_attachment_is_image( $image_id ) ) return;

        $attachment  = get_post( $image_id );
        $term_genre  = 'All';

        // Extract taken date from EXIF first, fallback to filename/metadata
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '../includes/class-ap-library-exif.php';
        $taken_date_from_exif = Ap_Library_EXIF::get_taken_date_from_post( $image_id );

        if ( $taken_date_from_exif ) {
            $timestamp  = strtotime( $taken_date_from_exif );
            $term_year  = date( 'Y', $timestamp );
            $term_month = date( 'm', $timestamp );
            $term_day   = date( 'd', $timestamp );
            $taken_date = $taken_date_from_exif;
        } else {
            list( $term_year, $term_month, $term_day ) = PhotoDateHelper::extract_date_terms( $image_id );
            if ( $term_year !== 'unknown' && $term_month && $term_day ) {
                $taken_date = sprintf( '%s-%s-%s', $term_year, $term_month, $term_day );
            } else {
                $taken_date = null;
            }
        }

        $genre_term_id = PhotoTermHelper::ensure_genre_term( $term_genre );
        $upload_date   = date( 'Y-m-d', strtotime( $attachment->post_date ) );

        // Tax input (date taxonomies sync from meta later)
        $tax_input = [];
        if ( ! empty( $genre_term_id ) ) {
            $tax_input['aplb_genre'] = [ $genre_term_id ];
        }

        // Build gallery
        $gallery_shortcode = '[gallery ids="' . $image_id . '"]';
        $meow_gallery_html = '<!-- wp:meow-gallery/gallery {"images":[{"alt":"","id":' . $image_id . ',"url":"' . esc_url( wp_get_attachment_url( $image_id ) ) . '","caption":""}]} -->'
            . $gallery_shortcode . '<!-- /wp:meow-gallery/gallery -->';

        $gallery_html = '<!-- wp:group --><div class="wp-block-group single-image">' . $meow_gallery_html . '</div><!-- /wp:group -->';

        // Create post
        $new_post = [
            'post_title'  => sanitize_text_field( $attachment->post_title ),
            'post_status' => 'pending',
            'post_author' => get_current_user_id(),
            'post_type'   => 'aplb_photo',
            'tax_input'   => $tax_input,
        ];

        $post_id = wp_insert_post( $new_post );
        if ( is_wp_error( $post_id ) ) return;

        set_post_thumbnail( $post_id, $image_id );

        // Meta & taxonomy sync
        update_post_meta( $post_id, APLB_META_PUBLISHED_DATE, $upload_date );
        $this->sync_date_to_taxonomy( $post_id, $upload_date, 'aplb_published_date' );

        if ( $taken_date ) {
            update_post_meta( $post_id, APLB_META_TAKEN_DATE, $taken_date );
            $this->sync_date_to_taxonomy( $post_id, $taken_date, 'aplb_taken_date' );
        }

        // Keywords
        $keywords = Ap_Library_EXIF::get_keywords( $image_id );
        if ( ! empty( $keywords ) ) {
            $this->assign_keywords( $post_id, $keywords );
        }

        // Link attachment to post
        wp_update_post( [ 'ID' => $image_id, 'post_parent' => $post_id ] );
        wp_update_post( [ 'ID' => $post_id, 'post_content' => $gallery_html ] );
    }

    /**
     * Assign keyword taxonomy terms to a post.
     *
     * @since 1.2.0
     * @param int   $post_id  Post ID.
     * @param array $keywords Array of keyword strings.
     */
    private function assign_keywords( $post_id, $keywords ) {
        $taxonomy = 'aplb_keyword';
        if ( ! taxonomy_exists( $taxonomy ) ) return;

        $term_ids = [];
        foreach ( $keywords as $kw ) {
            $kw = sanitize_text_field( $kw );
            if ( $kw === '' ) continue;

            $slug     = sanitize_title( strtolower( $kw ) );
            $existing = get_term_by( 'slug', $slug, $taxonomy );
            if ( ! $existing ) {
                $name    = $this->format_keyword_name( $slug );
                $created = wp_insert_term( $name, $taxonomy, [ 'slug' => $slug ] );
                if ( ! is_wp_error( $created ) ) {
                    $term_ids[] = (int) $created['term_id'];
                }
            } else {
                $term_ids[] = (int) $existing->term_id;
            }
        }
        if ( ! empty( $term_ids ) ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
        }
    }

    /**
     * Sync date meta to taxonomy.
     *
     * @param int    $post_id  Post ID.
     * @param string $date     YYYY-MM-DD.
     * @param string $taxonomy Taxonomy.
     */
    private function sync_date_to_taxonomy( $post_id, $date, $taxonomy ) {
        if ( ! $date || ! taxonomy_exists( $taxonomy ) ) return;
        $term_id = ( $taxonomy === 'aplb_taken_date' ) ? $this->sync_hierarchical_date( $date, $taxonomy ) : $this->sync_flat_date( $date, $taxonomy );
        if ( $term_id ) {
            wp_set_object_terms( $post_id, [ $term_id ], $taxonomy, false );
        }
    }

    /**
     * Flat date taxonomy sync.
     */
    private function sync_flat_date( $date, $taxonomy ) {
        $term = get_term_by( 'slug', $date, $taxonomy );
        if ( ! $term ) {
            $timestamp = strtotime( $date );
            $term_name = $timestamp ? date_i18n( 'F j, Y', $timestamp ) : $date;
            $result    = wp_insert_term( $term_name, $taxonomy, [ 'slug' => $date ] );
            if ( is_wp_error( $result ) ) return null;
            return $result['term_id'];
        }
        return $term->term_id;
    }

    /**
     * Hierarchical date taxonomy sync (Year -> Month -> Day).
     */
    private function sync_hierarchical_date( $date, $taxonomy ) {
        $timestamp = strtotime( $date );
        if ( ! $timestamp ) return null;

        $year  = date( 'Y', $timestamp );
        $month = date( 'm', $timestamp );
        $day   = date( 'd', $timestamp );

        $year_term = get_term_by( 'slug', $year, $taxonomy );
        if ( ! $year_term ) {
            $year_result = wp_insert_term( $year, $taxonomy, [ 'slug' => $year ] );
            if ( is_wp_error( $year_result ) ) return null;
            $year_term_id = $year_result['term_id'];
        } else { $year_term_id = $year_term->term_id; }

        $month_slug = $year . '-' . $month;
        $month_term = get_term_by( 'slug', $month_slug, $taxonomy );
        if ( ! $month_term ) {
            $month_result = wp_insert_term( date_i18n( 'F Y', $timestamp ), $taxonomy, [ 'slug' => $month_slug, 'parent' => $year_term_id ] );
            if ( is_wp_error( $month_result ) ) return null;
            $month_term_id = $month_result['term_id'];
        } else { $month_term_id = $month_term->term_id; }

        $day_slug = $date;
        $day_term = get_term_by( 'slug', $day_slug, $taxonomy );
        if ( ! $day_term ) {
            $day_result = wp_insert_term( date_i18n( 'F j, Y', $timestamp ), $taxonomy, [ 'slug' => $day_slug, 'parent' => $month_term_id ] );
            if ( is_wp_error( $day_result ) ) return null;
            return $day_result['term_id'];
        }
        return $day_term->term_id;
    }

    /**
     * Title-case keyword display name.
     */
    private function format_keyword_name( $keyword ) {
        $keyword = str_replace( [ '-', '_' ], ' ', $keyword );
        return ucwords( $keyword );
    }
}