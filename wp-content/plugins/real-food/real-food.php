<?php
/**
 * Plugin Name: Real Food
 * Description: Serverrenderade receptlistor för Real Food by Axenhorn.
 * Version: 0.2.0
 */
defined('ABSPATH') || exit;

add_shortcode('real_food_recipes', function ($attributes) {
    $attributes = shortcode_atts(array('collection' => 'all'), $attributes, 'real_food_recipes');
    $collection = $attributes['collection'];
    if (!in_array($collection, array('all', 'sous-vide'), true)) {
        return '';
    }
    $page = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $query = new WP_Query(array(
        'post_type' => $collection === 'all' ? array('wprm_recipe', 'recipe') : array('recipe'),
        'post_status' => 'publish',
        'posts_per_page' => $collection === 'all' ? 20 : 12,
        'orderby' => $collection === 'all' ? 'date' : 'rand',
        'order' => 'DESC',
        'paged' => $page,
        'ignore_sticky_posts' => true,
    ));
    ob_start();
    ?>
    <style>
    .rf-recipes{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr));gap:24px;margin:24px 0}
    .rf-recipe{min-width:0;border:1px solid #dedbd4;background:#fff}
    .rf-recipe a{display:block;color:inherit;text-decoration:none}
    .rf-recipe a:focus-visible{outline:3px solid #97472f;outline-offset:4px}
    .rf-recipe img{display:block;width:100%;height:200px;object-fit:cover}
    .rf-recipe h2{font-size:1.2rem;line-height:1.4;margin:16px}
    .rf-pages{display:flex;gap:12px;flex-wrap:wrap;margin:24px 0}
    .rf-pages .page-numbers{padding:8px 12px;border:1px solid #dedbd4}
    </style>
    <div class="rf-recipes">
    <?php
    foreach ($query->posts as $recipe_post) {
        $id = $recipe_post->ID;
        $public_id = $id;
        if ($recipe_post->post_password) { continue; }
        $url = get_permalink($id);
        $title = get_the_title($id);
        $image_id = get_post_thumbnail_id($id);
        // WPRM owns the parent-page URL and image of its internal recipe records.
        if ($recipe_post->post_type === 'wprm_recipe') {
            if (!class_exists('WPRM_Recipe_Manager')) {
                continue;
            }
            $recipe = WPRM_Recipe_Manager::get_recipe($id);
            if (!$recipe || !method_exists($recipe, 'parent_post_id')) {
                continue;
            }
            $parent_id = (int) $recipe->parent_post_id();
            if (!$parent_id || get_post_status($parent_id) !== 'publish' || get_post_field('post_password', $parent_id)) {
                continue;
            }
            $public_id = $parent_id;
            $url = get_permalink($parent_id);
            if (method_exists($recipe, 'image_id')) {
                $image_id = $recipe->image_id();
            }
        }
        if (!$url) {
            continue;
        }
        ?>
        <article class="rf-recipe">
            <a href="<?php echo esc_url($url); ?>">
                <?php if ($image_id) { echo wp_get_attachment_image($image_id, 'medium_large', false, array('loading' => 'lazy')); } ?>
                <h2><?php echo esc_html($title); ?></h2>
            </a>
            <?php echo rf_card_details_html($public_id); ?>
        </article>
        <?php
    }
    ?>
    </div>
    <?php
    if (!$query->post_count) {
        echo '<p>Inga recept hittades.</p>';
    }
    $links = paginate_links(array(
        'current' => $page,
        'total' => $query->max_num_pages,
        'prev_text' => '« Tidigare',
        'next_text' => 'Nästa »',
        'type' => 'array',
    ));
    if ($links) {
        echo '<nav class="rf-pages" aria-label="Receptsidor">' . wp_kses_post(implode('', $links)) . '</nav>';
    }
    return ob_get_clean();
});

/** Read presentation data through the existing recipe formats; never invent missing values. */
function rf_card_details($post_id) {
    static $cache = array();
    if (array_key_exists($post_id, $cache)) { return $cache[$post_id]; }
    $post = get_post($post_id);
    $data = array('summary'=>'', 'count'=>0, 'average'=>0, 'times'=>array());
    if (!$post || $post->post_status !== 'publish' || $post->post_password) { return $cache[$post_id] = false; }
    if ($post->post_type === 'recipe') {
        $data['summary'] = get_post_meta($post_id, 'recipe_description', true);
        if (class_exists('WPURP_User_Ratings')) {
            $rating = WPURP_User_Ratings::get_recipe_rating($post_id);
            $data['count'] = isset($rating['votes']) ? (int) $rating['votes'] : 0;
            $data['average'] = isset($rating['rating']) ? (float) $rating['rating'] : 0;
        }
        // Legacy times have free-text units and can overlap: retain their actual labels.
        foreach (array('prep'=>'Förberedelse', 'cook'=>'Tillagning', 'passive'=>'Väntetid') as $key=>$label) {
            $value = trim((string) get_post_meta($post_id, 'recipe_' . $key . '_time', true));
            $unit = trim((string) get_post_meta($post_id, 'recipe_' . $key . '_time_text', true));
            if ($value !== '' && (!is_numeric($value) || (float) $value > 0)) {
                $data['times'][] = $label . ': ' . trim($value . ' ' . $unit);
            }
        }
    } else {
        if (!class_exists('WPRM_Recipe_Manager')) { return $cache[$post_id] = false; }
        $ids = WPRM_Recipe_Manager::get_recipe_ids_from_post($post_id);
        if (!$ids) { return $cache[$post_id] = false; }
        $recipe = WPRM_Recipe_Manager::get_recipe(reset($ids));
        if (!$recipe) { return $cache[$post_id] = false; }
        $data['summary'] = $recipe->summary();
        $rating = $recipe->rating();
        $data['count'] = isset($rating['count']) ? (int) $rating['count'] : 0;
        $data['average'] = isset($rating['average']) ? (float) $rating['average'] : 0;
        $total = (float) $recipe->total_time();
        if ($total > 0) {
            $data['times'][] = 'Total tid: ' . rf_card_duration($total);
        } elseif ((float) $recipe->cook_time() > 0) {
            $data['times'][] = 'Tillagning: ' . rf_card_duration((float) $recipe->cook_time());
        }
    }
    if (!trim(wp_strip_all_tags((string) $data['summary']))) { $data['summary'] = $post->post_excerpt; }
    $data['summary'] = wp_trim_words(wp_strip_all_tags(strip_shortcodes((string) $data['summary'])), 22, '…');
    return $cache[$post_id] = $data;
}

function rf_card_duration($minutes) {
    $minutes = (int) ceil($minutes);
    $days = intdiv($minutes, 1440);
    $hours = intdiv($minutes % 1440, 60);
    $rest = $minutes % 60;
    $parts = array();
    if ($days) { $parts[] = $days . ' dygn'; }
    if ($hours) { $parts[] = $hours . ' tim'; }
    if ($rest) { $parts[] = $rest . ' min'; }
    return implode(' ', $parts);
}

function rf_card_details_html($post_id) {
    $data = rf_card_details($post_id);
    if (!$data) { return ''; }
    $html = '<div class="rf-card-details">';
    if ($data['summary']) { $html .= '<p class="rf-card-summary">' . esc_html($data['summary']) . '</p>'; }
    $html .= '<p class="rf-card-rating">';
    if ($data['count'] > 0 && $data['average'] >= 1 && $data['average'] <= 5) {
        $html .= '<span aria-hidden="true">★ </span>' . esc_html(number_format_i18n($data['average'], 2)) . ' av 5 · ' . esc_html($data['count']) . ' betyg';
    } else { $html .= 'Inga betyg ännu'; }
    $html .= '</p>';
    foreach ($data['times'] as $time) { $html .= '<p class="rf-card-time">' . esc_html($time) . '</p>'; }
    return $html . '</div>';
}
