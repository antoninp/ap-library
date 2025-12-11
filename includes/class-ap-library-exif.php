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

	/**
	 * Extract keywords from image IPTC metadata.
	 *
	 * @since 1.2.0
	 * @param int $attachment_id Attachment ID.
	 * @return array Array of sanitized keyword strings.
	 */
	public static function get_keywords( $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return [];
		}

		$keywords = [];
		// Use getimagesize with APP13 to read IPTC segment
		$info = [];
		@getimagesize( $file_path, $info );
		if ( ! empty( $info['APP13'] ) && function_exists( 'iptcparse' ) ) {
			$iptc = @iptcparse( $info['APP13'] );
			if ( ! empty( $iptc['2#025'] ) && is_array( $iptc['2#025'] ) ) {
				foreach ( $iptc['2#025'] as $raw_kw ) {
					$kw = trim( $raw_kw );
					if ( $kw !== '' ) {
						$keywords[] = sanitize_text_field( $kw );
					}
				}
			}
		}

		// Deduplicate and limit excessive keyword counts (safety)
		$keywords = array_values( array_unique( $keywords ) );
		if ( count( $keywords ) > 50 ) {
			$keywords = array_slice( $keywords, 0, 50 );
		}
		return $keywords;
	}

	/**
	 * Extract keywords from a post's featured image.
	 *
	 * @since 1.2.0
	 * @param int $post_id Post ID.
	 * @return array Array of keyword strings.
	 */
	public static function get_keywords_from_post( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return [];
		}
		return self::get_keywords( $thumbnail_id );
	}

	/**
	 * Extract location from image IPTC metadata.
	 *
	 * @since 1.3.2
	 * @param int $attachment_id Attachment ID.
	 * @return string Location string or empty string if not found.
	 */
	public static function get_location( $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return '';
		}

		// Use getimagesize with APP13 to read IPTC segment
		$info = [];
		@getimagesize( $file_path, $info );
		if ( ! empty( $info['APP13'] ) && function_exists( 'iptcparse' ) ) {
			$iptc = @iptcparse( $info['APP13'] );
			
			// IPTC 2#101: Country/Primary Location Name
			// IPTC 2#092: Sublocation (City)
			// IPTC 2#090: City
			// IPTC 2#095: Province/State
			
			$location_parts = [];
			
			// City/Sublocation
			if ( ! empty( $iptc['2#092'][0] ) ) {
				$location_parts[] = trim( $iptc['2#092'][0] );
			} elseif ( ! empty( $iptc['2#090'][0] ) ) {
				$location_parts[] = trim( $iptc['2#090'][0] );
			}
			
			// Province/State
			if ( ! empty( $iptc['2#095'][0] ) ) {
				$location_parts[] = trim( $iptc['2#095'][0] );
			}
			
			// Country
			if ( ! empty( $iptc['2#101'][0] ) ) {
				$location_parts[] = trim( $iptc['2#101'][0] );
			}
			
			if ( ! empty( $location_parts ) ) {
				return sanitize_text_field( implode( ', ', $location_parts ) );
			}
		}

		return '';
	}

	/**
	 * Extract location from a post's featured image.
	 *
	 * @since 1.3.2
	 * @param int $post_id Post ID.
	 * @return string Location string or empty string if not found.
	 */
	public static function get_location_from_post( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( ! $thumbnail_id ) {
			return '';
		}
		return self::get_location( $thumbnail_id );
	}
}
