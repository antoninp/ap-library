<?php

require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

/**
 * CreateAllLibrary class that implements ActionInterface.
 * This action processes all uploads and creates missing library posts for each date/genre.
 */
class CreateAllLibrary implements ActionInterface {
    use LibraryActionHelpers;

    /**
     * Execute the action.
     *
     * @return WP_Error|true
     */
    public function execute() {
        $uploads = get_posts([
            'post_type'      => 'aplb_uploads',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);
        if (empty($uploads)) {
            return new WP_Error('ap_library_error', 'No uploads found.');
        }

        // Group uploads by pdate and genre
        $uploads_by_date_genre = [];
        foreach ($uploads as $upload) {
            $pdates = wp_get_post_terms($upload->ID, 'aplb_library_pdate', ['fields' => 'slugs']);
            $genres = wp_get_post_terms($upload->ID, 'aplb_uploads_genre', ['fields' => 'ids']);
            if (empty($pdates) || empty($genres)) {
                continue;
            }
            foreach ($pdates as $pdate) {
                foreach ($genres as $genre) {
                    $uploads_by_date_genre[$pdate][$genre][] = $upload;
                }
            }
        }

        $created = 0;
        foreach ($uploads_by_date_genre as $pdate => $genres) {
            $pdate_term = term_exists($pdate, 'aplb_library_pdate');
            $pdate_term_id = ($pdate_term && is_array($pdate_term)) ? $pdate_term['term_id'] : false;
            if (!$pdate_term_id) continue;

            foreach ($genres as $genre_id => $genre_uploads) {
                // Check if a library post exists for this date/genre
                $existing_posts = $this->get_library_post_for_genre_and_date($genre_id, $pdate);
                if (empty($existing_posts)) {
                    $result = $this->create_new_library_post($genre_id, $genre_uploads, $pdate, $pdate_term_id);
                    if ($result) {
                        $created++;
                    }
                }
            }
        }

        if ($created) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No new aplb_library posts created.');
        }
    }

}