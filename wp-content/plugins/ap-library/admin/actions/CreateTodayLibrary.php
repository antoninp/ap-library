<?php
<?php
require_once __DIR__ . '/ActionInterface.php';
require_once __DIR__ . '/LibraryActionHelpers.php';

/**
 * CreateTodayLibrary class that implements ActionInterface.
 * This action processes today's uploads and creates or updates library posts.
 */
class CreateTodayLibrary implements ActionInterface {
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
}