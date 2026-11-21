<?php
/**
 * Plugin Name: Real Food
 * Description: Serverrenderade receptlistor för Real Food by Axenhorn.
 * Version: 0.1.0
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
            if (!$parent_id || get_post_status($parent_id) !== 'publish') {
                continue;
            }
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
