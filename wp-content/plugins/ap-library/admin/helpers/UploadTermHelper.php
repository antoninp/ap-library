<?php

class UploadTermHelper {
    public static function ensure_tdate_terms($year, $month, $day) {
        if ($year === 'unknown') {
            $unknown_term = term_exists('unknown', 'aplb_uploads_tdate');
            $unknown_term_id = ($unknown_term && is_array($unknown_term)) ? $unknown_term['term_id'] : 0;
            if (!$unknown_term_id) {
                $new_unknown = wp_insert_term('unknown', 'aplb_uploads_tdate');
                $unknown_term_id = !is_wp_error($new_unknown) ? $new_unknown['term_id'] : 0;
            }
            // Only return the unknown term, no month/day
            return [$unknown_term_id, null, null];
        }

        // Year
        $year_term = term_exists($year, 'aplb_uploads_tdate');
        $year_term_id = ($year_term && is_array($year_term)) ? $year_term['term_id'] : 0;
        if (!$year_term_id) {
            $new_year = wp_insert_term($year, 'aplb_uploads_tdate');
            $year_term_id = !is_wp_error($new_year) ? $new_year['term_id'] : 0;
        }

        // Month
        $month_slug = $year . '-' . $month;
        $month_term = term_exists($month_slug, 'aplb_uploads_tdate');
        $month_term_id = ($month_term && is_array($month_term)) ? $month_term['term_id'] : 0;
        if (!$month_term_id) {
            $new_month = wp_insert_term($month_slug, 'aplb_uploads_tdate', [
                'parent' => $year_term_id,
                'description' => $year . '-' . $month
            ]);
            $month_term_id = !is_wp_error($new_month) ? $new_month['term_id'] : 0;
        }

        // Day
        $day_slug = $year . '-' . $month . '-' . $day;
        $day_term = term_exists($day_slug, 'aplb_uploads_tdate');
        $day_term_id = ($day_term && is_array($day_term)) ? $day_term['term_id'] : 0;
        if (!$day_term_id) {
            $new_day = wp_insert_term($day_slug, 'aplb_uploads_tdate', [
                'parent' => $month_term_id,
                'description' => $year . '-' . $month . '-' . $day
            ]);
            $day_term_id = !is_wp_error($new_day) ? $new_day['term_id'] : 0;
        }

        return [$year_term_id, $month_term_id, $day_term_id];
    }

    public static function ensure_genre_term($genre) {
        $existing_term = term_exists($genre, 'aplb_uploads_genre');
        if ($existing_term && is_array($existing_term)) {
            return $existing_term['term_id'];
        }
        $new_term = wp_insert_term($genre, 'aplb_uploads_genre');
        return !is_wp_error($new_term) ? $new_term['term_id'] : 0;
    }

    public static function ensure_pdate_term($date) {
        $pdate_term = term_exists($date, 'aplb_library_pdate');
        if ($pdate_term && is_array($pdate_term)) {
            return $pdate_term['term_id'];
        }
        $new_pdate = wp_insert_term($date, 'aplb_library_pdate');
        return !is_wp_error($new_pdate) ? $new_pdate['term_id'] : 0;
    }
}