<?php
require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

/**
 * ProcessLibrary class that implements ActionInterface.
 * This action processes today's uploads and creates or updates library posts.
 */
class ProcessLibrary implements ActionInterface {
    use LibraryActionHelpers;

    public function execute() {
        $today = date('Y-m-d');
        $pdate_term_id = $this->get_today_pdate_term_id($today);
        if (!$pdate_term_id) {
            return new WP_Error('ap_library_error', 'No uploads found for today.');
        }

        $uploads = $this->get_uploads_for_today($pdate_term_id);
        if (empty($uploads)) {
            return new WP_Error('ap_library_error', 'No uploads found for today.');
        }

        $uploads_by_genre = $this->group_uploads_by_genre($uploads);

        $created = 0;
        foreach ($uploads_by_genre as $genre_id => $genre_uploads) {
            $existing_posts = $this->get_library_post_for_genre_today($genre_id, $today);

            // Only create if no post exists for today/genre
            if (empty($existing_posts)) {
                $result = $this->create_new_library_post($genre_id, $genre_uploads, $today, $pdate_term_id);
                if ($result) {
                    $created++;
                }
            }
        }

        if ($created) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No new aplb_library posts created for today.');
        }
    }

    private function create_new_library_post($genre_id, $genre_uploads, $today, $pdate_term_id) {
        $image_ids = [];
        $images_json = [];
        foreach ($genre_uploads as $upload) {
            $thumb_id = get_post_thumbnail_id($upload->ID);
            if ($thumb_id) {
                $image_ids[] = $thumb_id;
                $images_json[] = [
                    'alt'     => '',
                    'id'      => $thumb_id,
                    'url'     => esc_url(wp_get_attachment_url($thumb_id)),
                    'caption' => ''
                ];
            }
        }
        $image_ids = array_unique($image_ids);
        if (empty($image_ids)) {
            return false;
        }

        $genre_term = $genre_id ? get_term($genre_id, 'aplb_uploads_genre') : null;
        $genre_name = $genre_term ? $genre_term->name : __('All', 'ap-library');
        $post_title = sprintf(__('%s - %s', 'ap-library'), $today, $genre_name);
        $gallery_html = $this->build_gallery_html($image_ids, $images_json);

        $new_post = [
            'post_title'    => $post_title,
            'post_content'  => $gallery_html,
            'post_status'   => 'publish',
            'post_type'     => 'aplb_library',
        ];
        $post_id = wp_insert_post($new_post);
        if ($post_id && $genre_id) {
            wp_set_post_terms($post_id, [$genre_id], 'aplb_uploads_genre', false);
        }
        if ($post_id && !empty($pdate_term_id)) {
            wp_set_post_terms($post_id, [$pdate_term_id], 'aplb_library_pdate', false);
        }
        return $post_id ? true : false;
    }
}