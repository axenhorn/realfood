<?php
/**
 * Plugin Name: Real Food
 * Description: Serverrenderade receptlistor för Real Food by Axenhorn.
 * Version: 0.3.0
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

/** Explicit staging-only URL migration. */
function rf_url_map() {
 return array(
1723 => array('old'=>'beef-jerky','new'=>'beef-jerky','type'=>'post','title'=>'Äkta Beef Jerky'),
320 => array('old'=>'recept-julskinka-sous-vide','new'=>'julskinka-sous-vide','type'=>'post','title'=>'Perfekt julskinka – Sous Vide'),
916 => array('old'=>'flankstek-sous-vide','new'=>'flankstek-sous-vide','type'=>'post','title'=>'Mör och saftig Flankstek Sous Vide'),
868 => array('old'=>'flapsteak-sous-vide','new'=>'flapsteak-sous-vide','type'=>'post','title'=>'Mör och saftig Flap steak Sous Vide'),
847 => array('old'=>'blue-cheese-dipp-en-amerikansk-klassiker','new'=>'blue-cheese-dipp-en-amerikansk-klassiker','type'=>'post','title'=>'Blue Cheese Dipp – En Amerikansk klassiker'),
838 => array('old'=>'kycklingvingar-med-honung-och-vitlok-sous-vide','new'=>'kycklingvingar-med-honung-och-vitlok-sous-vide','type'=>'post','title'=>'Kycklingvingar med honung och vitlök – Sous Vide'),
824 => array('old'=>'pilgrimsmusslor-sous-vide','new'=>'pilgrimsmusslor-sous-vide','type'=>'post','title'=>'Perfekta pilgrimsmusslor – Sous Vide'),
753 => array('old'=>'oxbringa-brisket-sous-vide','new'=>'oxbringa-brisket-sous-vide','type'=>'recipe','title'=>'Oxbringa (Brisket) Sous Vide'),
741 => array('old'=>'trip-tip-sous-vide-barbecue','new'=>'trip-tip-sous-vide-barbecue','type'=>'recipe','title'=>'Trip Tip Sous Vide barbecue'),
707 => array('old'=>'inlagd-chili-hela','new'=>'inlagd-chili-hela','type'=>'recipe','title'=>'Inlagd Chili (hela)'),
684 => array('old'=>'egg-bites-sous-vide','new'=>'egg-bites-sous-vide','type'=>'recipe','title'=>'Egg Bites Sous Vide'),
671 => array('old'=>'picanha-sous-vide','new'=>'picanha-sous-vide','type'=>'recipe','title'=>'Picanha Sous Vide'),
649 => array('old'=>'habanerohotsauce','new'=>'habanerohotsauce','type'=>'recipe','title'=>'Habanero Hot Sauce'),
498 => array('old'=>'recept-chokladmousse','new'=>'chokladmousse','type'=>'recipe','title'=>'Chokladmousse'),
470 => array('old'=>'recept-tonfisk-sous-vide','new'=>'tonfisk-sous-vide','type'=>'recipe','title'=>'Saftig tonfisk – Sous Vide'),
455 => array('old'=>'kalkonbrost-sous-vide','new'=>'kalkonbrost-sous-vide','type'=>'recipe','title'=>'Kalkonbröst – Sous Vide'),
443 => array('old'=>'recept-lax-sous-vide','new'=>'lax-sous-vide','type'=>'recipe','title'=>'Saftig lax – Sous Vide'),
429 => array('old'=>'lammracks-sous-vide','new'=>'lammracks-sous-vide','type'=>'recipe','title'=>'Lammracks – Sous Vide'),
423 => array('old'=>'recept-majs-sous-vide','new'=>'majs-sous-vide','type'=>'recipe','title'=>'Majs – Sous Vide'),
396 => array('old'=>'recept-bearnaisesas','new'=>'bearnaisesas','type'=>'recipe','title'=>'Bearnaisesås'),
389 => array('old'=>'recept_hollandaisesas','new'=>'hollandaisesas','type'=>'recipe','title'=>'Hollandaisesås'),
372 => array('old'=>'recept-graddsas-brunsas','new'=>'graddsas-brunsas','type'=>'recipe','title'=>'Riktig gräddsås (brunsås)'),
324 => array('old'=>'recept-inlagd-rodlok','new'=>'inlagd-rodlok','type'=>'recipe','title'=>'Picklad rödlök'),
284 => array('old'=>'pulled-pork-sous-vide','new'=>'pulled-pork-sous-vide','type'=>'recipe','title'=>'Pulled Pork – Sous Vide'),
222 => array('old'=>'recept-kimchi','new'=>'kimchi','type'=>'recipe','title'=>'Kimchi'),
156 => array('old'=>'recept-inlagd-jalapeno','new'=>'inlagd-jalapeno','type'=>'recipe','title'=>'Inlagd jalapeno'),
148 => array('old'=>'pulled-pork','new'=>'pulled-pork','type'=>'recipe','title'=>'Pulled Pork'),
130 => array('old'=>'recept-pico-de-gallo','new'=>'pico-de-gallo','type'=>'recipe','title'=>'Pico de Gallo'),
125 => array('old'=>'mangosalsa','new'=>'mangosalsa','type'=>'recipe','title'=>'Mangosalsa'),
112 => array('old'=>'recept-ceviche','new'=>'ceviche','type'=>'recipe','title'=>'Ceviche'),
7 => array('old'=>'recept-citron-och-ingefaravatten','new'=>'citron-och-ingefaravatten','type'=>'recipe','title'=>'Citron- och ingefäravatten'),
219 => array('old'=>'recept-inlagd-vitlok','new'=>'inlagd-vitlok','type'=>'recipe','title'=>'Picklad eller inlagd vitlök'),
 );
}

function rf_url_stage() {
    return untrailingslashit(home_url()) === 'https://www.axenhorn.se/stg_45384';
}
function rf_url_active() { return rf_url_stage() && get_option('rf_url_trial_active', false); }
function rf_url_rules() {
    if (!rf_url_active()) { return; }
    foreach (rf_url_map() as $id => $row) {
        add_rewrite_rule('^' . preg_quote($row['new'], '#') . '/?$', 'index.php?post_type=' . $row['type'] . '&p=' . $id, 'top');
    }
}
add_action('init', 'rf_url_rules', 99);
add_filter('post_type_link', function ($url, $post) {
    $map = rf_url_map();
    return rf_url_active() && $post->post_status === 'publish' && isset($map[$post->ID]) ? home_url('/' . $map[$post->ID]['new'] . '/') : $url;
}, 99, 2);
function rf_url_redirect_map() {
    $rules = array();
    foreach (rf_url_map() as $row) {
        $target = '/' . $row['new'] . '/';
        foreach (array('/' . $row['old'] . '/', '/recipe/' . $row['old'] . '/') as $source) {
            if ($source !== $target) { $rules[$source] = $target; }
        }
    }
    return $rules;
}
add_action('template_redirect', function () {
    if (!rf_url_active() || !in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'), true)) { return; }
    $path = wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = untrailingslashit(wp_parse_url(home_url(), PHP_URL_PATH));
    if (strpos($path, $base . '/') !== 0) { return; }
    $path = trailingslashit(substr($path, strlen($base)));
    $rules = rf_url_redirect_map();
    if (isset($rules[$path])) { wp_safe_redirect(home_url($rules[$path]), 301, 'Real Food'); exit; }
}, -100);
// Update rendered editorial links without modifying saved recipe content.
add_filter('the_content', function ($content) {
    if (!rf_url_active()) { return $content; }
    $origins = array(home_url(), 'https://www.axenhorn.se', 'https://axenhorn.se', 'http://www.axenhorn.se', 'http://axenhorn.se');
    foreach (rf_url_map() as $row) {
        foreach (array('/' . $row['old'] . '/', '/recipe/' . $row['old'] . '/') as $path) {
            foreach ($origins as $origin) {
                foreach (array('"', "'") as $quote) {
                    $content = str_replace('href=' . $quote . $origin . $path . $quote, 'href=' . $quote . esc_url(home_url('/' . $row['new'] . '/')) . $quote, $content);
                }
            }
        }
    }
    return $content;
}, 99);
function rf_url_restore($backup) {
    update_option('rf_url_trial_active', false);
    $errors = array();
    foreach (array_reverse($backup['posts']) as $fields) {
        $result = wp_update_post(wp_slash($fields), true);
        if (is_wp_error($result)) { $errors[] = $result->get_error_message(); }
    }
    if (isset($backup['legacy_name'])) { update_post_meta(429, 'recipe_title', wp_slash($backup['legacy_name'])); }
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure($backup['permalink']);
    foreach (rf_url_map() as $row) { unset($wp_rewrite->extra_rules_top['^' . preg_quote($row['new'], '#') . '/?$']); }
    flush_rewrite_rules();
    return $errors;
}
add_action('admin_menu', function () {
    if (rf_url_stage()) { add_management_page('Real Food URL-test', 'Real Food URL-test', 'manage_options', 'rf-url-trial', 'rf_url_admin'); }
});
function rf_url_admin() {
    if (!rf_url_stage() || !current_user_can('manage_options')) { return; }
    $errors = array(); $message = '';
    if (isset($_POST['rf_url_action'])) {
        check_admin_referer('rf_url_trial');
        $action = sanitize_key(wp_unslash($_POST['rf_url_action']));
        if ($action === 'restore' && ($backup = get_option('rf_url_trial_backup'))) {
            $errors = rf_url_restore($backup);
            if (!$errors) { $message = 'Ursprungliga titlar, slugs och permalänkar återställda.'; }
        } elseif ($action === 'apply' && !rf_url_active()) {
            global $wpdb, $wp_rewrite;
            $posts = array(); $map = rf_url_map();
            // These image attachment pages use the desired recipe slugs. Image files stay unchanged.
            $attachments = array(471 => array('old'=>'tonfisk-sous-vide', 'new'=>'tonfisk-sous-vide-bild', 'parent'=>470), 1126 => array('old'=>'kimchi', 'new'=>'kimchi-bild', 'parent'=>222));
            foreach ($attachments as $id => $row) {
                $post = get_post($id);
                if (!$post || $post->post_type !== 'attachment' || $post->post_name !== $row['old'] || (int) $post->post_parent !== $row['parent']) { $errors[] = 'Oväntad bildpost ' . $id; continue; }
                $taken = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND ID != %d AND post_status NOT IN ('trash','auto-draft') LIMIT 1", $row['new'], $id));
                if ($taken) { $errors[] = 'Bildadress upptagen: ' . $row['new']; }
                $posts[] = array('ID'=>$id, 'post_title'=>$post->post_title, 'post_name'=>$post->post_name);
            }
            foreach ($map as $id => $row) {
                $post = get_post($id);
                if (!$post || $post->post_type !== $row['type'] || $post->post_status !== 'publish' || $post->post_name !== $row['old']) {
                    $errors[] = 'Oväntat ursprungsläge för recept ' . $id; continue;
                }
                $collision = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND ID != %d AND post_type IN ('post','page','recipe','attachment') AND post_status NOT IN ('trash','auto-draft') LIMIT 1", $row['new'], $id));
                if ($collision && !isset($attachments[(int) $collision])) { $errors[] = 'Adresskollision med post ' . $collision . ': ' . $row['new']; }
                $posts[] = array('ID'=>$id, 'post_title'=>$post->post_title, 'post_name'=>$post->post_name);
            }
            $wprm = get_post(1749);
            if (!$wprm || $wprm->post_type !== 'wprm_recipe') { $errors[] = 'Julskinkans WPRM-recept saknas.'; }
            else { $posts[] = array('ID'=>1749, 'post_title'=>$wprm->post_title, 'post_name'=>$wprm->post_name); }
            if (!$errors) {
                $backup = array('created'=>gmdate('c'), 'permalink'=>get_option('permalink_structure'), 'posts'=>$posts);
                if (!get_option('rf_url_trial_backup')) { add_option('rf_url_trial_backup', $backup, '', false); }
                $saved_backup = get_option('rf_url_trial_backup');
                if (!$saved_backup) { $errors[] = 'Kunde inte spara återställningsdata.'; }
                else {
                    if (!isset($saved_backup['legacy_name'])) {
                        $saved_backup['legacy_name'] = get_post_meta(429, 'recipe_title', true);
                        update_option('rf_url_trial_backup', $saved_backup, false);
                    }
                    $backup['legacy_name'] = $saved_backup['legacy_name'];
                }
            }
            if (!$errors) {
                foreach ($posts as $fields) {
                    $id = $fields['ID'];
                    if (isset($attachments[$id])) { $fields['post_name'] = $attachments[$id]['new']; }
                    elseif (isset($map[$id])) { $fields['post_title'] = $map[$id]['title']; $fields['post_name'] = $map[$id]['new']; }
                    else { $fields['post_title'] = preg_replace('/^Recept\s+/u', '', $fields['post_title']); $fields['post_title'] = ucfirst($fields['post_title']); }
                    $result = wp_update_post(wp_slash($fields), true);
                    if (is_wp_error($result) || get_post_field('post_name', $id) !== $fields['post_name']) { $errors[] = 'Kunde inte uppdatera post ' . $id; break; }
                }
                if ($errors) { $errors = array_merge($errors, rf_url_restore($backup)); }
                else {
                    update_post_meta(429, 'recipe_title', 'Lammracks - Sous Vide');
                    update_option('rf_url_trial_active', true, false);
                    $wp_rewrite->set_permalink_structure('/%postname%/');
                    rf_url_rules(); flush_rewrite_rules();
                    $message = 'URL-testet är aktiverat. 32 recept har kontrollerats och uppdaterats. Återställningsdata finns sparade.';
                }
            }
        }
    }
    echo '<div class="wrap"><h1>Real Food URL-test</h1><p>Endast testmiljön. Produktionssajten påverkas inte.</p>';
    if ($message) { echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>'; }
    foreach ($errors as $error) { echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>'; }
    echo '<p>Status: <strong>' . (rf_url_active() ? 'Aktiverat' : 'Ej aktiverat') . '</strong></p><form method="post">';
    wp_nonce_field('rf_url_trial');
    if (!rf_url_active()) { echo '<button class="button button-primary" name="rf_url_action" value="apply">Aktivera URL-test</button> '; }
    if (get_option('rf_url_trial_backup')) { echo '<button class="button" name="rf_url_action" value="restore">Återställ ursprungsläget</button>'; }
    echo '</form><h2>Receptens slutadresser</h2><ul>';
    foreach (rf_url_map() as $row) { echo '<li><a href="' . esc_url(home_url('/' . $row['new'] . '/')) . '">' . esc_html($row['title']) . '</a></li>'; }
    echo '</ul></div>';
}
