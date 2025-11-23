<?php

require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/../services/PhotoPostCreator.php';

/**
 * Action that scans all images and creates aplb_photo posts for any missing.
 */
class CreateAllPhotoPosts implements ActionInterface {
    /**
     * Execute the action.
     *
     * @return WP_Error|true
     */
    public function execute() {
        $images = get_posts([
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        if ( empty( $images ) ) {
            return new WP_Error( 'ap_library_error', 'No images found in media library.' );
        }

        $existing_photos = get_posts([
            'post_type'      => 'aplb_photo',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        $existing_image_ids = [];
        foreach ( $existing_photos as $photo_id ) {
            $thumb_id = get_post_thumbnail_id( $photo_id );
            if ( $thumb_id ) {
                $existing_image_ids[] = $thumb_id;
            }
        }

        $created            = 0;
        $creator            = new PhotoPostCreator();
        
        // Get filter settings from options
        $exclude_keywords_str = get_option( 'ap_library_exclude_keywords', 'logo,banner,icon,avatar,profile,thumbnail,thumb,background,header,footer,placeholder,default,button,badge,sprite,ui,favicon,symbol,graphic,decoration' );
        $exclude_keywords     = array_filter( array_map( 'trim', explode( ',', $exclude_keywords_str ) ) );
        $min_width            = (int) get_option( 'ap_library_min_photo_width', 400 );
        $min_height           = (int) get_option( 'ap_library_min_photo_height', 400 );
        $min_filesize_kb      = (int) get_option( 'ap_library_min_photo_filesize', 50 );
        $exclude_extensions   = [ 'svg', 'gif' ];
        
        foreach ( $images as $image_id ) {
            $file     = get_attached_file( $image_id );
            $filename = strtolower( basename( $file ) );
            $ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

            // Check filename against exclusion keywords
            foreach ( $exclude_keywords as $word ) {
                if ( ! empty( $word ) && strpos( $filename, strtolower( $word ) ) !== false ) {
                    continue 2; // Skip this image entirely
                }
            }
            
            // Check extension exclusions
            if ( in_array( $ext, $exclude_extensions, true ) ) continue;

            // Check dimensions
            if ( $min_width > 0 || $min_height > 0 ) {
                $metadata = wp_get_attachment_metadata( $image_id );
                if ( isset( $metadata['width'], $metadata['height'] ) ) {
                    if ( $min_width > 0 && $metadata['width'] < $min_width ) continue;
                    if ( $min_height > 0 && $metadata['height'] < $min_height ) continue;
                }
            }
            
            // Check file size
            if ( $min_filesize_kb > 0 && file_exists( $file ) ) {
                $filesize_kb = filesize( $file ) / 1024;
                if ( $filesize_kb < $min_filesize_kb ) continue;
            }

            if ( ! in_array( $image_id, $existing_image_ids, true ) ) {
                $creator->create_post_on_image_upload( $image_id, true );
                $created++;
            }
        }

        if ( $created ) {
            return true;
        }
        return new WP_Error( 'ap_library_error', 'No new photo posts created. All images already have photo posts or were excluded.' );
    }
}