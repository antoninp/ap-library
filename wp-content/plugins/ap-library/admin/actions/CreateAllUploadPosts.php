<?php

require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/../services/UploadPostCreator.php';

class CreateAllUploadPosts implements ActionInterface {
    public function execute() {
        // Get all image attachments
        $images = get_posts([
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        if (empty($images)) {
            return new WP_Error('ap_library_error', 'No images found in media library.');
        }

        // Find all aplb_uploads posts and their attached image IDs
        $existing_uploads = get_posts([
            'post_type'      => 'aplb_uploads',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        $existing_image_ids = [];
        foreach ($existing_uploads as $upload_id) {
            $thumb_id = get_post_thumbnail_id($upload_id);
            if ($thumb_id) {
                $existing_image_ids[] = $thumb_id;
            }
        }

        $created = 0;
        $creator = new UploadPostCreator();
        $exclude_keywords = ['logo', 'banner', 'icon', 'avatar', 'profile', 'thumbnail', 'thumb', 'background', 'header', 'footer', 'placeholder', 'default'];
        $exclude_extensions = ['svg', 'gif']; // Add extensions you want to skip
        foreach ($images as $image_id) {
            $file = get_attached_file($image_id);
            $filename = strtolower(basename($file));
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            // Exclude by keyword
            foreach ($exclude_keywords as $word) {
                if (strpos($filename, $word) !== false) {
                    continue 2; // Skip this image
                }
            }
            // Exclude by extension
            if (in_array($ext, $exclude_extensions)) {
                continue;
            }
            if (!in_array($image_id, $existing_image_ids)) {
                $creator->create_post_on_image_upload($image_id, true);
                $created++;
            }
        }

        if ($created) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No new upload posts created. All images already have upload posts or were excluded.');
        }
    }
}