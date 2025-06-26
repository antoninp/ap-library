<?php
require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

class UpdateAllLibraryPosts implements ActionInterface {
    use LibraryActionHelpers;

    public function execute() {
        $args = [
            'post_type'      => 'aplb_library',
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => -1,
        ];
        $library_posts = get_posts($args);

        if (empty($library_posts)) {
            return new WP_Error('ap_library_error', 'No library posts found.');
        }

        $updated = 0;
        foreach ($library_posts as $library_post) {
            // Get the genre/category term for this post
            $genre_terms = wp_get_post_terms($library_post->ID, 'aplb_library_category', ['fields' => 'ids']);
            $genre_id = !empty($genre_terms) ? $genre_terms[0] : 0;

            // Get the pdate term for this post (if you want to update by date as well)
            $pdate_terms = wp_get_post_terms($library_post->ID, 'aplb_library_pdate', ['fields' => 'names']);
            $date = !empty($pdate_terms) ? $pdate_terms[0] : null;

            if (!$date) {
                continue; // Skip if no date is set
            }

            // Get uploads for this genre and date
            $pdate_term_id = $this->get_today_pdate_term_id($date);
            if (!$pdate_term_id) {
                continue;
            }
            $uploads = $this->get_uploads_for_today($pdate_term_id);
            $uploads_by_genre = $this->group_uploads_by_genre($uploads);

            $genre_uploads = isset($uploads_by_genre[$genre_id]) ? $uploads_by_genre[$genre_id] : [];
            if (empty($genre_uploads)) {
                continue;
            }

            $result = $this->update_existing_library_post($library_post, $genre_uploads, $genre_id, $pdate_term_id);
            if ($result) {
                $updated++;
            }
        }

        if ($updated) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No aplb_library posts updated.');
        }
    }
}