<?php

/**
 * Helper class for managing taxonomy terms related to photos.
 *
 * Provides static methods to ensure the existence of specific
 * taxonomy terms used in the plugin (taken date hierarchy, genre, published date).
 *
 * @link       https://antoninpuleo.com
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin/helpers
 */
class PhotoTermHelper {
    /**
     * Ensure the existence of year, month, and day terms in the 'aplb_taken_date' taxonomy.
     *
     * @since 1.0.0
     * @param string $year  The year (e.g., '2024' or 'unknown').
     * @param string $month The month (e.g., '07' for July).
     * @param string $day   The day (e.g., '15').
     * @return array        Term IDs for year, month, and day (month/day may be null for unknown year).
     */
    public static function ensure_tdate_terms( $year, $month, $day ) {
        if ( $year === 'unknown' ) {
            $unknown_term    = term_exists( 'unknown', 'aplb_taken_date' );
            $unknown_term_id = ( $unknown_term && is_array( $unknown_term ) ) ? $unknown_term['term_id'] : 0;
            if ( ! $unknown_term_id ) {
                $new_unknown    = wp_insert_term( 'unknown', 'aplb_taken_date' );
                $unknown_term_id = ! is_wp_error( $new_unknown ) ? $new_unknown['term_id'] : 0;
            }
            return [ $unknown_term_id, null, null ];
        }

        // Year
        $year_term    = term_exists( $year, 'aplb_taken_date' );
        $year_term_id = ( $year_term && is_array( $year_term ) ) ? $year_term['term_id'] : 0;
        if ( ! $year_term_id ) {
            $new_year    = wp_insert_term( $year, 'aplb_taken_date' );
            $year_term_id = ! is_wp_error( $new_year ) ? $new_year['term_id'] : 0;
        }

        // Month
        $month_slug    = $year . '-' . $month;
        $month_term    = term_exists( $month_slug, 'aplb_taken_date' );
        $month_term_id = ( $month_term && is_array( $month_term ) ) ? $month_term['term_id'] : 0;
        if ( ! $month_term_id ) {
            $new_month    = wp_insert_term( $month_slug, 'aplb_taken_date', [
                'parent'      => $year_term_id,
                'description' => $year . '-' . $month,
            ] );
            $month_term_id = ! is_wp_error( $new_month ) ? $new_month['term_id'] : 0;
        }

        // Day
        $day_slug    = $year . '-' . $month . '-' . $day;
        $day_term    = term_exists( $day_slug, 'aplb_taken_date' );
        $day_term_id = ( $day_term && is_array( $day_term ) ) ? $day_term['term_id'] : 0;
        if ( ! $day_term_id ) {
            $new_day    = wp_insert_term( $day_slug, 'aplb_taken_date', [
                'parent'      => $month_term_id,
                'description' => $year . '-' . $month . '-' . $day,
            ] );
            $day_term_id = ! is_wp_error( $new_day ) ? $new_day['term_id'] : 0;
        }

        return [ $year_term_id, $month_term_id, $day_term_id ];
    }

    /**
     * Ensure the existence of a genre term in the 'aplb_genre' taxonomy.
     *
     * @since 1.0.0
     * @param string $genre The genre name (e.g., 'All').
     * @return int          Term ID of the genre or 0 on failure.
     */
    public static function ensure_genre_term( $genre ) {
        $existing_term = term_exists( $genre, 'aplb_genre' );
        if ( $existing_term && is_array( $existing_term ) ) {
            return $existing_term['term_id'];
        }
        $new_term = wp_insert_term( $genre, 'aplb_genre' );
        return ! is_wp_error( $new_term ) ? $new_term['term_id'] : 0;
    }

    /**
     * Ensure the existence of a published date term in the 'aplb_published_date' taxonomy.
     *
     * @since 1.0.0
     * @param string $date Date in 'Y-m-d' format or 'unknown'.
     * @return int         Term ID for the date term or 0 on failure.
     */
    public static function ensure_pdate_term( $date ) {
        $timestamp = strtotime( $date );
        if ( $timestamp && $date !== 'unknown' ) {
            $human_name = date_i18n( 'F j, Y', $timestamp );
            $slug       = date( 'Y-m-d', $timestamp );
        } else {
            $human_name = __( 'Unknown', 'ap-library' );
            $slug       = 'unknown';
        }

        $pdate_term = term_exists( $slug, 'aplb_published_date' );
        if ( $pdate_term && is_array( $pdate_term ) ) {
            return $pdate_term['term_id'];
        }
        $new_pdate = wp_insert_term( $human_name, 'aplb_published_date', [
            'slug'        => $slug,
            'description' => $human_name,
        ] );
        return ! is_wp_error( $new_pdate ) ? $new_pdate['term_id'] : 0;
    }
}