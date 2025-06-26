<?php
trait LibraryActionHelpers {
    private function get_today_pdate_term_id($today) {
        $pdate_term = term_exists($today, 'aplb_library_pdate');
        return ($pdate_term && is_array($pdate_term)) ? $pdate_term['term_id'] : false;
    }

    private function get_uploads_for_today($pdate_term_id) {
        return get_posts([
            'post_type'      => 'aplb_uploads',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'tax_query'      => [[
                'taxonomy' => 'aplb_library_pdate',
                'field'    => 'term_id',
                'terms'    => $pdate_term_id,
            ]],
        ]);
    }

    private function group_uploads_by_genre($uploads) {
        $uploads_by_genre = [];
        foreach ($uploads as $upload) {
            $genres = wp_get_post_terms($upload->ID, 'aplb_uploads_genre', ['fields' => 'ids']);
            if (empty($genres)) $genres = [0]; // Use 0 for "All"
            foreach ($genres as $genre_id) {
                $uploads_by_genre[$genre_id][] = $upload;
            }
        }
        return $uploads_by_genre;
    }

    private function get_or_create_library_category_id($genre_id) {
        if ($genre_id && $genre_id !== 0) {
            $genre_term = get_term($genre_id, 'aplb_uploads_genre');
            $genre_name = $genre_term ? $genre_term->name : __('All', 'ap-library');
            $genre_slug = $genre_term ? $genre_term->slug : 'all';
        } else {
            $genre_name = __('All', 'ap-library');
            $genre_slug = 'all';
        }
        $library_cat_term = term_exists($genre_slug, 'aplb_library_category');
        if ($library_cat_term && is_array($library_cat_term)) {
            return $library_cat_term['term_id'];
        } else {
            $new_cat = wp_insert_term($genre_name, 'aplb_library_category', array('slug' => $genre_slug));
            return !is_wp_error($new_cat) ? $new_cat['term_id'] : 0;
        }
    }

    private function get_library_post_for_genre_today($genre_id, $today) {
        $args = array(
            'post_type'      => 'aplb_library',
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => 1,
            'date_query'     => array(
                array(
                    'after'     => $today . ' 00:00:00',
                    'before'    => $today . ' 23:59:59',
                    'inclusive' => true,
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'aplb_library_category',
                    'field'    => 'term_id',
                    'terms'    => $genre_id,
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        return get_posts($args);
    }

    private function build_gallery_html($image_ids, $images_json) {
        $gallery_class = (count($image_ids) === 1) ? 'single-image' : '';
        $gallery_shortcode = '[gallery ids="' . implode(',', $image_ids) . '" layout="tiles"]';
        return '<!-- wp:group {"className":"' . esc_attr($gallery_class) . '"} -->' .
            '<div class="wp-block-group ' . esc_attr($gallery_class) . '">' .
                '<!-- wp:meow-gallery/gallery ' . json_encode([
                    'images' => $images_json,
                    'layout' => 'tiles'
                ]) . ' -->' .
                $gallery_shortcode .
                '<!-- /wp:meow-gallery/gallery -->' .
            '</div>' .
        '<!-- /wp:group -->';
    }

    public function update_existing_library_post($library_post, $genre_uploads, $library_cat_id, $pdate_term_id) {
        $image_ids = [];
        $images_json = [];
        foreach ($genre_uploads as $upload) {
            // Only include published uploads
            if (get_post_status($upload->ID) !== 'publish') {
                continue;
            }
            $thumb_id = get_post_thumbnail_id($upload->ID);
            if ($thumb_id && get_post_status($thumb_id) === 'publish') {
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

        // Remove unpublished images from the gallery
        $existing_content = $library_post->post_content;
        preg_match('/\[gallery ids="([^"]*)"/', $existing_content, $matches);
        $existing_ids = isset($matches[1]) ? array_map('intval', explode(',', $matches[1])) : [];
        // Only keep images that are still published and in the new set
        $merged_ids = array_intersect($existing_ids, $image_ids);
        // Add any new published images
        $merged_ids = array_unique(array_merge($merged_ids, $image_ids));

        // If no images remain, optionally set content to empty or a notice
        if (empty($merged_ids)) {
            $gallery_html = '';
        } else {
            $merged_images_json = [];
            foreach ($merged_ids as $id) {
                $merged_images_json[] = [
                    'alt'     => '',
                    'id'      => $id,
                    'url'     => esc_url(wp_get_attachment_url($id)),
                    'caption' => ''
                ];
            }
            $gallery_html = $this->build_gallery_html($merged_ids, $merged_images_json);
        }

        // Update the aplb_library post
        wp_update_post([
            'ID'           => $library_post->ID,
            'post_content' => $gallery_html,
        ]);
        // Update taxonomy
        if ($library_cat_id) {
            wp_set_post_terms($library_post->ID, [$library_cat_id], 'aplb_library_category', false);
        }
        if (!empty($pdate_term_id)) {
            wp_set_post_terms($library_post->ID, [$pdate_term_id], 'aplb_library_pdate', false);
        }
        return true;
    }
}