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
 wp_enqueue_style('real-food-theme', get_stylesheet_uri(), array(), '0.1.0');
});
add_filter('comments_open', '__return_false', 100);
add_filter('pings_open', '__return_false', 100);
add_filter('comments_array', '__return_empty_array', 100);
add_action('admin_menu', function () { remove_menu_page('edit-comments.php'); });
add_action('wp_before_admin_bar_render', function () {
 global $wp_admin_bar;
 $wp_admin_bar->remove_node('comments');
});
function rf_theme_navigation() {
 echo '<a href="'.esc_url(home_url('/alla-recept/')).'">Alla recept</a>';
 echo '<a href="'.esc_url(home_url('/sous-vide/')).'">Sous vide</a>';
 $term = get_term_by('name', 'Grillat', 'category');
 if ($term && !is_wp_error($term)) {
  echo '<a href="'.esc_url(get_term_link($term)).'">Grillat & BBQ</a>';
 }
 echo '<a href="'.esc_url(home_url('/sous-vide-temperaturer-och-tider/')).'">Temperaturer & tider</a>';
}
