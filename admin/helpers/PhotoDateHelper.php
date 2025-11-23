<?php
/**
 * Helper class for extracting date information from image filenames or metadata for photos.
 *
 * @link       https://antoninpuleo.com
 * @since      1.3.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/admin/helpers
 */
class PhotoDateHelper {
    /**
     * Extract date components from the image filename or metadata.
     * Expected filename pattern: YYYYMMDD-... (first 8 chars form date)
     * Falls back to image metadata created timestamp; otherwise returns unknown.
     *
     * @since  1.3.0
     * @param  int   $image_id Attachment ID of the image.
     * @return array [year, month, day] or ['unknown', null, null].
     */
    public static function extract_date_terms( $image_id ) {
        $full_path = get_attached_file( $image_id );
        $filename  = basename( $full_path, '.' . pathinfo( $full_path, PATHINFO_EXTENSION ) );
        $parts     = explode( '-', $filename );

        if ( isset( $parts[0] ) && preg_match( '/^\d{8}/', $parts[0] ) ) {
            $term_slug = sanitize_title( $parts[0] );
            return [
                substr( $term_slug, 0, 4 ),
                substr( $term_slug, 4, 2 ),
                substr( $term_slug, 6, 2 ),
            ];
        }

        $meta = wp_read_image_metadata( $full_path );
        if ( ! empty( $meta['created_timestamp'] ) ) {
            return [
                date( 'Y', $meta['created_timestamp'] ),
                date( 'm', $meta['created_timestamp'] ),
                date( 'd', $meta['created_timestamp'] ),
            ];
        }

        return [ 'unknown', null, null ];
    }
}