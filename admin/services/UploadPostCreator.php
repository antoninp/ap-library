<?php
require_once plugin_dir_path(__FILE__) . '../helpers/UploadDateHelper.php';
require_once plugin_dir_path(__FILE__) . '../helpers/UploadTermHelper.php';

/**
 * Class responsible for creating posts when images are uploaded.
 */
class UploadPostCreator {

    /**
     * Create a new 'aplb_uploads' post when an image is uploaded.
     *
     * @param int  $image_id The ID of the uploaded image.
     * @param bool $force    Whether to force post creation regardless of settings.
     */
    public function create_post_on_image_upload($image_id, $force = false) {
        // Only check the option if not forcing
        if (!$force && !get_option('ap_library_auto_create_post_on_upload', false)) return;
        if (!wp_attachment_is_image($image_id)) return;

        $attachment = get_post($image_id);
        $term_genre = 'All';

        // 1. Extract date terms
        list($term_year, $term_month, $term_day) = UploadDateHelper::extract_date_terms($image_id);

        // 2. Ensure taxonomy terms exist
        list($year_term_id, $month_term_id, $day_term_id) = UploadTermHelper::ensure_tdate_terms($term_year, $term_month, $term_day);
        $genre_term_id = UploadTermHelper::ensure_genre_term($term_genre);

        $upload_date = date('Y-m-d', strtotime($attachment->post_date));
        $pdate_term_id = UploadTermHelper::ensure_pdate_term($upload_date);

        // 3. Build tax_input
        $tax_input = [];
        $aplb_uploads_tdate_terms = array_filter([$year_term_id, $month_term_id, $day_term_id]);
        if (!empty($aplb_uploads_tdate_terms)) {
            $tax_input['aplb_uploads_tdate'] = $aplb_uploads_tdate_terms;
        }
        if (!empty($genre_term_id)) {
            $tax_input['aplb_uploads_genre'] = [$genre_term_id];
        }
        if (!empty($pdate_term_id)) {
            $tax_input['aplb_library_pdate'] = [$pdate_term_id];
        }

        // 4. Build gallery
        $gallery_shortcode = '[gallery ids="' . $image_id . '"]';
        $meow_gallery_html = '<!-- wp:meow-gallery/gallery {
            "images": [{
                "alt":"",
                "id":'. $image_id . ',
                "url":"'. esc_url(wp_get_attachment_url($image_id)) .'",
                "caption":""
                }]} -->
                '. $gallery_shortcode .'
            <!-- /wp:meow-gallery/gallery -->';
        
        // Wrap the meow-gallery in a group block with single-image class
        $gallery_html = '<!-- wp:group -->
            <div class="wp-block-group single-image">
                ' . $meow_gallery_html . '
            </div>
            <!-- /wp:group -->';

        // 5. Create post
        $new_post = [
            'post_title'    => sanitize_text_field($attachment->post_title),
            'post_status'   => 'pending',
            'post_author'   => get_current_user_id(),
            'post_type'     => 'aplb_uploads',
            'tax_input'     => $tax_input,
        ];

        $post_id = wp_insert_post($new_post);
        if (is_wp_error($post_id)) return;

        set_post_thumbnail($post_id, $image_id);

        $attachment_args = [
            'ID'           => $image_id,
            'post_parent'  => $post_id
        ];
        wp_update_post($attachment_args);

        wp_update_post([
            'ID'           => $post_id,
            'post_content' => $gallery_html
        ]);
    }
}