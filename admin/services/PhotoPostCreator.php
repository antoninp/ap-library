<?php
require_once plugin_dir_path( __FILE__ ) . '../helpers/PhotoDateHelper.php';
require_once plugin_dir_path( __FILE__ ) . '../helpers/PhotoTermHelper.php';

/**
 * Class responsible for creating photo posts when images are uploaded.
 *
 * @since      1.3.0
 * @modified   1.3.2 Location term assignment from IPTC metadata.
 */
class PhotoPostCreator {

    /**
     * Create a new 'aplb_photo' post when an image is uploaded.
     *
     * @since    1.3.0
     * @modified 1.3.2 Location assignment from IPTC, bulk date sync improvements.
     * @param    int  $image_id The ID of the uploaded image.
     * @param    bool $force    Whether to force post creation regardless of settings.
     * @return   void
     */
    public function create_post_on_image_upload( $image_id, $force = false ) {
        if ( ! $force && ! get_option( 'ap_library_auto_create_post_on_upload', false ) ) return;
        if ( ! wp_attachment_is_image( $image_id ) ) return;

        $attachment  = get_post( $image_id );

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

        $upload_date   = date( 'Y-m-d', strtotime( $attachment->post_date ) );

        // Create post
        $new_post = [
            'post_title'  => sanitize_text_field( $attachment->post_title ),
            'post_status' => 'pending',
            'post_author' => get_current_user_id(),
            'post_type'   => 'aplb_photo',
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

        // Location
        $location = Ap_Library_EXIF::get_location( $image_id );
        if ( ! empty( $location ) ) {
            $this->assign_location( $post_id, $location );
        }

        // Link attachment to post
        wp_update_post( [ 'ID' => $image_id, 'post_parent' => $post_id ] );
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
     * Assign location taxonomy term to a post.
     * Parses location string (e.g., "City, Region, Country") into hierarchical terms.
     *
     * @since 1.3.2
     * @param int    $post_id  Post ID.
     * @param string $location Location string from IPTC.
     */
    private function assign_location( $post_id, $location ) {
        $taxonomy = 'aplb_location';
        if ( ! taxonomy_exists( $taxonomy ) ) return;

        // Parse location string (e.g., "Paris, Île-de-France, France")
        $parts = array_map( 'trim', explode( ',', $location ) );
        $parts = array_filter( $parts ); // Remove empty parts
        
        if ( empty( $parts ) ) return;

        // Reverse array to go from broadest (Country) to most specific (City)
        $parts = array_reverse( $parts );
        
        $parent_id = 0;
        $term_id = null;
        
        foreach ( $parts as $part ) {
            $part = sanitize_text_field( $part );
            $slug = sanitize_title( strtolower( $part ) );
            
            // Check if term exists at this hierarchy level with the correct parent
            $existing = null;
            $all_terms = get_terms( [
                'taxonomy' => $taxonomy,
                'slug' => $slug,
                'hide_empty' => false,
            ] );
            
            if ( ! empty( $all_terms ) && ! is_wp_error( $all_terms ) ) {
                // Find term with matching parent
                foreach ( $all_terms as $term ) {
                    if ( (int) $term->parent === $parent_id ) {
                        $existing = $term;
                        break;
                    }
                }
            }
            
            if ( ! $existing ) {
                // Create new term
                $created = wp_insert_term( $part, $taxonomy, [ 
                    'slug' => $slug,
                    'parent' => $parent_id 
                ] );
                
                if ( ! is_wp_error( $created ) ) {
                    $term_id = (int) $created['term_id'];
                    $parent_id = $term_id;
                }
            } else {
                $term_id = (int) $existing->term_id;
                $parent_id = $term_id;
            }
        }
        
        // Assign the most specific term (leaf node) to the post
        if ( $term_id ) {
            wp_set_object_terms( $post_id, [ $term_id ], $taxonomy, false );
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
            $format = get_option( 'ap_library_date_format', 'M j, Y' );
            $term_name = $timestamp ? date_i18n( $format, $timestamp ) : $date;
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

        $format = get_option( 'ap_library_date_format', 'M j, Y' );
        
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
            $day_result = wp_insert_term( date_i18n( $format, $timestamp ), $taxonomy, [ 'slug' => $day_slug, 'parent' => $month_term_id ] );
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