<?php
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
 add_theme_support('title-tag');
 add_theme_support('post-thumbnails');
 add_theme_support('responsive-embeds');
 add_theme_support('html5', array('search-form','gallery','caption','style','script'));
 register_nav_menus(array('primary'=>'Huvudmeny'));
});
add_action('wp_enqueue_scripts', function () {
 wp_enqueue_style('real-food-theme', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
});
add_filter('comments_open', '__return_false', 100);
add_filter('pings_open', '__return_false', 100);
add_filter('comments_array', '__return_empty_array', 100);
add_action('admin_menu', function () { remove_menu_page('edit-comments.php'); });
add_action('wp_before_admin_bar_render', function () {
 global $wp_admin_bar;
 $wp_admin_bar->remove_node('comments');
});
/**
 * Resolve pages through WordPress so plain and pretty permalinks both work,
 * including installations in a staging subdirectory.
 */
function rf_theme_page_url($slug) {
 $page = get_page_by_path($slug, OBJECT, 'page');
 if ($page && 'publish' === get_post_status($page)) {
  return get_permalink($page);
 }
 return home_url('/');
}
function rf_theme_navigation() {
 echo '<a href="'.esc_url(rf_theme_page_url('alla-recept')).'">Alla recept</a>';
 echo '<a href="'.esc_url(rf_theme_page_url('sous-vide')).'">Sous vide</a>';
 $term = get_term_by('name', 'Grillat', 'category');
 if ($term && !is_wp_error($term)) {
  echo '<a href="'.esc_url(get_term_link($term)).'">Grillat & BBQ</a>';
 }
 echo '<a href="'.esc_url(rf_theme_page_url('sous-vide-temperaturer-och-tider')).'">Temperaturer & tider</a>';
}

/** Resolve the recipe image and anchor without changing plugin-owned recipe data. */
function rf_theme_recipe_data($post_id) {
 $post = get_post($post_id);
 if (!$post || post_password_required($post)) { return false; }
 $image = (int) get_post_thumbnail_id($post_id);
 if ('recipe' === $post->post_type) {
  return array('image' => $image, 'anchor' => 'wpurp-container-recipe-' . $post_id);
 }
 if (!class_exists('WPRM_Recipe_Manager') || !method_exists('WPRM_Recipe_Manager', 'get_recipe_ids_from_post')) { return false; }
 $ids = WPRM_Recipe_Manager::get_recipe_ids_from_post($post_id);
 if (!$ids) { return false; }
 $recipe = WPRM_Recipe_Manager::get_recipe(reset($ids));
 if (!$recipe) { return false; }
 if (!$image && method_exists($recipe, 'image_id')) { $image = (int) $recipe->image_id(); }
 return array('image' => $image, 'anchor' => 'wprm-recipe-container-' . (int) reset($ids));
}

function rf_theme_recipe_intro($post_id, $data) {
 if (!$data) { return; }
 echo '<div class="rf-recipe-intro">';
 if (function_exists('rf_card_details_html')) { echo rf_card_details_html($post_id); }
 echo '<a class="button" href="#' . esc_attr($data['anchor']) . '">Gå till recept</a>';
 if ($data['image']) {
  echo '<figure class="rf-main-image">' . wp_get_attachment_image($data['image'], 'large', false, array(
   'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 940px) 100vw, 900px',
  )) . '</figure>';
 }
 echo '</div>';
}

/** Relevant public recipe pages sharing categories/tags, never internal WPRM records. */
function rf_theme_related_recipes($post_id) {
 $categories = wp_get_post_categories($post_id);
 $tags = wp_get_post_tags($post_id, array('fields' => 'ids'));
 $default_category = (int) get_option('default_category');
 $categories = array_values(array_diff($categories, array($default_category)));
 $tax_query = array('relation' => 'OR');
 if ($categories) { $tax_query[] = array('taxonomy'=>'category','field'=>'term_id','terms'=>$categories); }
 if ($tags) { $tax_query[] = array('taxonomy'=>'post_tag','field'=>'term_id','terms'=>$tags); }
 if (count($tax_query) < 2) { return; }
 $query = new WP_Query(array(
  'post_type'=>array('post','recipe'), 'post_status'=>'publish', 'has_password'=>false,
  'post__not_in'=>array($post_id), 'posts_per_page'=>40, 'no_found_rows'=>true,
  'ignore_sticky_posts'=>true, 'tax_query'=>$tax_query, 'orderby'=>'date', 'order'=>'DESC',
 ));
 $items = array();
 foreach ($query->posts as $candidate) {
  $data = rf_theme_recipe_data($candidate->ID);
  if (!$data) { continue; }
  $score = count(array_intersect($tags, wp_get_post_tags($candidate->ID, array('fields'=>'ids')))) * 3
   + count(array_intersect($categories, wp_get_post_categories($candidate->ID)));
  $items[] = array('post'=>$candidate, 'data'=>$data, 'score'=>$score);
 }
 usort($items, function($a, $b) {
  $score = $b['score'] <=> $a['score'];
  return $score ?: ($b['post']->ID <=> $a['post']->ID);
 });
 if (!$items) { return; }
 echo '<section class="rf-related" aria-labelledby="rf-related-title"><h2 id="rf-related-title">Fler recept att prova</h2><div class="rf-related-grid">';
 foreach (array_slice($items, 0, 4) as $item) {
  $id = $item['post']->ID;
  echo '<article><a href="' . esc_url(get_permalink($id)) . '">';
  if ($item['data']['image']) { echo wp_get_attachment_image($item['data']['image'], 'medium_large', false, array('loading'=>'lazy')); }
  echo '<h3>' . esc_html(get_the_title($id)) . '</h3></a>';
  if (function_exists('rf_card_details_html')) { echo rf_card_details_html($id); }
  echo '</article>';
 }
 echo '</div></section>';
}


/** General-page design; individual recipe layout is a separate iteration. */
add_filter('body_class', function ($classes) {
 $classes[] = 'rf-studio';
 if (is_singular() && rf_theme_recipe_data(get_queried_object_id())) { $classes[] = 'rf-studio-recipe'; }
 return $classes;
});
function rf_studio_search() {
 echo '<form class="studio-search" role="search" method="get" action="' . esc_url(home_url('/')) . '"><label class="screen-reader-text" for="studio-query">Sök recept</label><input id="studio-query" name="s" type="search" placeholder="Vad vill du laga?" value="' . esc_attr(get_search_query()) . '"><input type="hidden" name="post_type[]" value="post"><input type="hidden" name="post_type[]" value="recipe"><button type="submit">Sök recept <span aria-hidden="true">→</span></button></form>';
}
function rf_studio_card($id) {
 $post = get_post($id); $data = rf_theme_recipe_data($id);
 if (!$post || $post->post_status !== 'publish' || $post->post_password || !$data) { return; }
 echo '<article class="studio-card"><a href="' . esc_url(get_permalink($id)) . '">';
 if ($data['image']) { echo wp_get_attachment_image($data['image'], 'medium_large', false, array('loading'=>'lazy','sizes'=>'(max-width: 600px) 100vw, (max-width: 900px) 50vw, 360px')); }
 echo '<h3>' . esc_html(get_the_title($id)) . '</h3></a>';
 if (function_exists('rf_card_details_html')) { echo rf_card_details_html($id); }
 echo '</article>';
}
function rf_studio_topics() {
 echo '<nav class="studio-topics" aria-label="Utforska recepten"><a href="' . esc_url(rf_theme_page_url('alla-recept')) . '">Alla recept</a><a href="' . esc_url(rf_theme_page_url('sous-vide')) . '">Sous vide</a>';
 $term = get_term_by('name', 'Grillat', 'category');
 if ($term && !is_wp_error($term)) { echo '<a href="' . esc_url(get_term_link($term)) . '">Grillat & BBQ</a>'; }
 echo '<a href="' . esc_url(rf_theme_page_url('sous-vide-temperaturer-och-tider')) . '">Temperaturer & tider</a></nav>';
}

/** Let the theme control legacy inline presentation, preserving recipe data and controls. */
add_filter('the_content', function ($html) {
 if (!is_singular('recipe') || !class_exists('DOMDocument')) { return $html; }
 $previous = libxml_use_internal_errors(true);
 $doc = new DOMDocument();
 $ok = $doc->loadHTML('<?xml encoding="utf-8" ?><div id="rf-content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
 if (!$ok) { libxml_clear_errors(); libxml_use_internal_errors($previous); return $html; }
 $xp = new DOMXPath($doc);
 foreach ($xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " wpurp-container ")]') as $root) {
  $root->removeAttribute('style');
  foreach ($xp->query('.//*[@style]', $root) as $node) { $node->removeAttribute('style'); }
 }
 $root = $doc->getElementById('rf-content-root');
 $out = '';
 if ($root) { foreach ($root->childNodes as $child) { $out .= $doc->saveHTML($child); } }
 libxml_clear_errors(); libxml_use_internal_errors($previous);
 return $out ?: $html;
}, 99);
