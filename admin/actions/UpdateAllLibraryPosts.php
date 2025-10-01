<?php
require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

/**
 * UpdateAllLibraryPosts class that implements ActionInterface.
 * This action updates all existing library posts based on their associated genres and publication dates.
 */
class UpdateAllLibraryPosts implements ActionInterface {
    use LibraryActionHelpers;

    /**
     * Execute the action.
     *
     * @return WP_Error|true
     */
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
            // Get all genre terms for this post
            $genre_terms = wp_get_post_terms($library_post->ID, 'aplb_uploads_genre', ['fields' => 'ids']);
            if (empty($genre_terms)) {
                continue;
            }

            // Get the pdate term for this post
            $pdate_terms = wp_get_post_terms($library_post->ID, 'aplb_library_pdate', ['fields' => 'names']);
            $date = !empty($pdate_terms) ? $pdate_terms[0] : null;

            if (!$date) {
                continue; // Skip if no date is set
            }

            // Get uploads for this date
            $pdate_term_id = $this->get_today_pdate_term_id($date);
            if (!$pdate_term_id) {
                continue;
            }
            $uploads = $this->get_uploads_for_today($pdate_term_id);
            $uploads_by_genre = $this->group_uploads_by_genre($uploads);

            // Loop through all genres for this library post
            foreach ($genre_terms as $genre_id) {
                $genre_uploads = isset($uploads_by_genre[$genre_id]) ? $uploads_by_genre[$genre_id] : [];
                if (empty($genre_uploads)) {
                    continue;
                }

                $result = $this->update_existing_library_post($library_post, $genre_uploads, $genre_id, $pdate_term_id);
                if ($result) {
                    $updated++;
                }
            }
        }

        if ($updated) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No aplb_library posts updated.');
        }
    }
}