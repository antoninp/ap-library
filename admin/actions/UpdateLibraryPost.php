<?php
require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

/**
 * UpdateLibraryPost class that implements ActionInterface.
 * This action processes today's uploads and updates existing library posts.
 */
class UpdateLibraryPost implements ActionInterface {
    use LibraryActionHelpers;

    /**
     * Execute the action.
     *
     * @return WP_Error|true
     */
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

        $updated = 0;
        foreach ($uploads_by_genre as $genre_id => $genre_uploads) {
            $existing_posts = $this->get_library_post_for_genre_today($genre_id, $today);

            if (!empty($existing_posts)) {
                $result = $this->update_existing_library_post($existing_posts[0], $genre_uploads, $genre_id, $pdate_term_id);
                if ($result) {
                    $updated++;
                }
            }
        }

        if ($updated) {
            return true;
        } else {
            return new WP_Error('ap_library_error', 'No aplb_library posts updated for today.');
        }
    }
}