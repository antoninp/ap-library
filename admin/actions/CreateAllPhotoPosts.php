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
        $exclude_keywords   = [ 'logo', 'banner', 'icon', 'avatar', 'profile', 'thumbnail', 'thumb', 'background', 'header', 'footer', 'placeholder', 'default' ];
        $exclude_extensions = [ 'svg', 'gif' ];
        foreach ( $images as $image_id ) {
            $file     = get_attached_file( $image_id );
            $filename = strtolower( basename( $file ) );
            $ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

            foreach ( $exclude_keywords as $word ) {
                if ( strpos( $filename, $word ) !== false ) {
                    continue 2; // Skip this image entirely
                }
            }
            if ( in_array( $ext, $exclude_extensions, true ) ) continue;

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