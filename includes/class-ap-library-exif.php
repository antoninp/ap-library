<?php

/**
 * EXIF data extraction for date fields.
 *
 * @link       https://antoninpuleo.com/
 * @since      1.0.0
 *
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 */

/**
 * Extracts EXIF data from uploaded images.
 *
 * @since      1.0.0
 * @package    Ap_Library
 * @subpackage Ap_Library/includes
 * @author     Antonin Puleo
 */
class Ap_Library_EXIF {

	/**
	 * Extract taken date from image EXIF data.
	 *
	 * @since    1.0.0
	 * @param    int    $attachment_id    Attachment ID.
	 * @return   string|false    ISO 8601 date string or false on failure.
	 */
	public static function get_taken_date( $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		// Check if EXIF functions are available
		if ( ! function_exists( 'exif_read_data' ) ) {
			return false;
		}

		// Suppress warnings for images without EXIF data
		$exif = @exif_read_data( $file_path );
		
		if ( ! $exif ) {
			return false;
		}

		// Try DateTimeOriginal first (when photo was taken)
		$date_string = false;
		if ( ! empty( $exif['DateTimeOriginal'] ) ) {
			$date_string = $exif['DateTimeOriginal'];
		} elseif ( ! empty( $exif['DateTime'] ) ) {
			$date_string = $exif['DateTime'];
		} elseif ( ! empty( $exif['DateTimeDigitized'] ) ) {
			$date_string = $exif['DateTimeDigitized'];
		}

		if ( ! $date_string ) {
			return false;
		}

		// EXIF date format is typically "YYYY:MM:DD HH:MM:SS"
		// Convert to ISO 8601 format "YYYY-MM-DD"
		$date_string = str_replace( ':', '-', substr( $date_string, 0, 10 ) );
		
		// Validate the date
		$parts = explode( '-', $date_string );
		if ( count( $parts ) === 3 && checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
			return $date_string;
		}

		return false;
	}

	/**
	 * Extract taken date from post's featured image.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id    Post ID.
	 * @return   string|false    ISO 8601 date string or false on failure.
	 */
	public static function get_taken_date_from_post( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		
		if ( ! $thumbnail_id ) {
			return false;
		}

		return self::get_taken_date( $thumbnail_id );
	}
}
