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
 if (function_exists('rf_card_details_html')) {
  $summary = get_post_meta($post_id, 'recipe_description', true);
  if ('recipe' !== get_post_type($post_id) && class_exists('WPRM_Recipe_Manager')) {
   $ids = WPRM_Recipe_Manager::get_recipe_ids_from_post($post_id);
   $recipe = $ids ? WPRM_Recipe_Manager::get_recipe(reset($ids)) : false;
   if ($recipe) { $summary = $recipe->summary(); }
  }
  $summary = trim(wp_strip_all_tags(strip_shortcodes((string) $summary)));
  $details = rf_card_details_html($post_id);
  if ($summary) {
   $details = preg_replace_callback('/<p class="rf-card-summary">.*?<\/p>/s', function () use ($summary) {
    return '<p class="rf-card-summary">' . esc_html($summary) . '</p>';
   }, $details, 1);
  }
  echo $details;
 }
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

/** A public, chronological recipe rail; never imply measured popularity. */
function rf_studio_sidebar() {
 $query = new WP_Query(array(
  'post_type'=>array('post','recipe'), 'post_status'=>'publish', 'has_password'=>false,
  'posts_per_page'=>80, 'no_found_rows'=>true, 'ignore_sticky_posts'=>true,
  'orderby'=>array('date'=>'DESC','ID'=>'DESC'),
  'post__not_in'=>is_singular() ? array(get_queried_object_id()) : array(),
 ));
 $ids = array();
 foreach ($query->posts as $candidate) {
  if (rf_theme_recipe_data($candidate->ID)) { $ids[] = $candidate->ID; }
  if (count($ids) === 4) { break; }
 }
 if (!$ids) { return; }
 echo '<aside class="studio-sidebar" aria-labelledby="studio-latest-title"><section class="studio-sidebar-recipes"><p class="eyebrow">NYTT I RECEPTBOKEN</p><h2 id="studio-latest-title">Senaste recepten</h2><div class="studio-sidebar-list">';
 foreach ($ids as $id) { rf_studio_card($id); }
 echo '</div><a class="studio-sidebar-all" href="' . esc_url(rf_theme_page_url('alla-recept')) . '">Utforska alla recept</a></section>';
 echo '<section class="studio-sidebar-guide"><p class="eyebrow">NÄRA TILL HANDS</p><h2>Rätt tid.<br>Rätt temperatur.</h2><p>Hitta tider och temperaturer inför nästa middag.</p><a href="' . esc_url(rf_theme_page_url('sous-vide-temperaturer-och-tider')) . '">Till sous vide-guiden</a></section></aside>';
}

/** Real Food import prototype: staging administrators only, no public publication. */
function rf_lab_text($value) {
 return is_scalar($value) ? trim(html_entity_decode(wp_strip_all_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
}
function rf_lab_steps($value) {
 if (is_string($value)) { return array_values(array_filter(array_map('rf_lab_text', preg_split('~<br\s*/?>|</p>|\r?\n~i', $value)))); }
 if (!is_array($value)) { return array(); }
 if (isset($value['itemListElement'])) { return rf_lab_steps($value['itemListElement']); }
 if (isset($value['text'])) { return array(rf_lab_text($value['text'])); }
 $out = array(); foreach ($value as $item) { $out = array_merge($out, rf_lab_steps($item)); } return $out;
}
function rf_lab_find($value, &$recipes, $depth = 0) {
 if (!is_array($value) || $depth > 30 || count($recipes) >= 10) { return; }
 foreach ((array)($value['@type'] ?? array()) as $type) {
  if (is_string($type) && preg_match('~(^|[/#])Recipe$~', $type)) { $recipes[] = $value; return; }
 }
 foreach ($value as $item) { rf_lab_find($item, $recipes, $depth + 1); }
}
function rf_lab_import($url) {
 $response = wp_safe_remote_get($url, array('timeout'=>18, 'redirection'=>3, 'limit_response_size'=>2000000, 'headers'=>array('Accept'=>'text/html,application/xhtml+xml'), 'user-agent'=>'RealFoodImportTest/0.1'));
 if (is_wp_error($response)) { throw new RuntimeException('Länken kunde inte hämtas. Klistra in recepttexten i stället.'); }
 if (wp_remote_retrieve_response_code($response) !== 200) { throw new RuntimeException('Källan tillåter inte hämtningen just nu. Klistra in recepttexten i stället.'); }
 $html = wp_remote_retrieve_body($response);
 if (strlen($html) >= 2000000) { throw new RuntimeException('Sidan är för stor för testimporten. Klistra in recepttexten.'); }
 $previous = libxml_use_internal_errors(true); $doc = new DOMDocument();
 $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET);
 libxml_clear_errors(); libxml_use_internal_errors($previous);
 $xpath = new DOMXPath($doc); $recipes = array();
 foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) { rf_lab_find(json_decode($script->textContent, true), $recipes); }
 if (!$recipes) { throw new RuntimeException('Inget läsbart Recipe-recept hittades. Det gäller ofta sociala inlägg och video. Klistra in bildtexten eller skriv receptet manuellt.'); }
 $out = array();
 foreach ($recipes as $r) {
  $yield = $r['recipeYield'] ?? ''; if (is_array($yield)) { $yield = $yield['value'] ?? $yield[0] ?? ''; }
  $ingredients = array(); foreach ((array)($r['recipeIngredient'] ?? array()) as $item) { if (is_scalar($item) && rf_lab_text($item) !== '') { $ingredients[] = rf_lab_text($item); } }
  $out[] = array('title'=>rf_lab_text($r['name'] ?? ''), 'description'=>rf_lab_text($r['description'] ?? ''), 'yield'=>rf_lab_text($yield), 'ingredients'=>implode("\n",$ingredients), 'steps'=>implode("\n",rf_lab_steps($r['recipeInstructions'] ?? array())), 'source'=>$url, 'method'=>'Webbsida · Recipe-data');
 }
 return $out;
}
add_action('template_redirect', function () {
 if (!isset($_GET['rf_import_lab'])) { return; }
 if (!str_contains((string)wp_parse_url(home_url('/'), PHP_URL_PATH), '/stg_') || !current_user_can('manage_options')) {
  status_header(403); nocache_headers(); wp_die('Importtestet är endast tillgängligt för inloggad administratör i testmiljön.', 'Privat importtest', array('response'=>403));
 }
 nocache_headers(); header('X-Robots-Tag: noindex, nofollow'); header('Referrer-Policy: no-referrer');
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!wp_verify_nonce($_SERVER['HTTP_X_RF_NONCE'] ?? '', 'rf-import-lab')) { wp_send_json_error('Sessionen har gått ut. Ladda om sidan.',403); }
  $payload = json_decode(file_get_contents('php://input', false, null, 0, 8192),true);
  $url = is_array($payload) && is_string($payload['url'] ?? null) ? trim($payload['url']) : '';
  if (strlen($url)>2048 || !wp_http_validate_url($url)) { wp_send_json_error('Ange en giltig offentlig http- eller https-länk.',400); }
  $key = 'rf_lab_rate_'.get_current_user_id();
  if (get_transient($key)) { wp_send_json_error('Vänta fem sekunder mellan importförsöken.',429); }
  set_transient($key,1,5);
  try { wp_send_json_success(rf_lab_import($url)); } catch (Throwable $e) { wp_send_json_error($e->getMessage(),422); }
 }
 $nonce = wp_json_encode(wp_create_nonce('rf-import-lab'));
 ?>
<!doctype html><html lang="sv"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Receptboken · Real Food importtest</title>
<style>
:root{--paper:#f7f2e8;--ink:#2f2f2b;--olive:#687750;--line:#d4d0c2}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:16px/1.6 system-ui,sans-serif}header,main{max-width:760px;margin:auto;padding:24px}header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--line)}.brand{font:25px Georgia,serif;letter-spacing:.04em}.brand small{display:block;font:10px system-ui;letter-spacing:.25em}header>span{font-size:13px;color:var(--olive)}h1,h2{font:normal 38px/1.15 Georgia,serif}h2{font-size:27px}p{margin:12px 0 22px}.eyebrow{font-size:12px;letter-spacing:.15em;color:var(--olive);margin-top:26px}.intro{color:#62645b}.panel{background:#fffcf6;border:1px solid var(--line);border-radius:12px;padding:23px;margin:24px 0}label{display:block;font-weight:600;margin:16px 0 7px}input,textarea,select,button{font:inherit}input,textarea,select{width:100%;padding:13px;border:1px solid #aaa995;background:white;border-radius:6px;color:var(--ink)}textarea{resize:vertical}button{padding:13px 18px;min-height:48px;border:1px solid var(--olive);border-radius:6px;background:var(--olive);color:white;cursor:pointer}button:disabled{opacity:.5;cursor:wait}.secondary{background:transparent;color:var(--olive)}.actions{display:flex;gap:10px;flex-wrap:wrap;margin:18px 0}.notice{font-size:14px;color:#62645b}.status{white-space:pre-line;color:#5c342b}.chips{display:flex;gap:8px;flex-wrap:wrap}.chips span{background:#e9ecdf;padding:5px 10px;border-radius:20px;font-size:13px}details summary{cursor:pointer;padding:12px 0}a{color:var(--olive)}.saved article{border-bottom:1px solid var(--line);padding:18px 0}.saved h3{font:24px Georgia,serif;margin:0 0 8px}:focus-visible{outline:3px solid #a34e35;outline-offset:3px}[hidden]{display:none!important}@media(max-width:500px){header,main{padding:20px}h1{font-size:35px}.panel{padding:18px}.actions button{flex:1}}
</style></head><body><header><div class="brand">REAL FOOD<small>DIN RECEPTBOK</small></div><span>Privat importtest · 0.1</span></header><main>
<p class="eyebrow">FRÅN INSPIRATION TILL MIDDAG</p><h1>Hittat något<br>du vill laga?</h1><p class="intro">Samla recept från webben och dina egna anteckningar. Granska först, spara sedan.</p><div class="chips"><span>Webbsidor</span><span>Inklistrad text</span><span>Egna recept</span></div>
<section class="panel"><h2>Lägg till ett recept</h2><form id="import-form"><label for="url">Länk till originalet</label><input id="url" type="url" required placeholder="https://…" autocomplete="off"><div class="actions"><button id="fetch">Hämta recept</button><button type="button" class="secondary" id="manual">Skriv själv</button></div></form><p class="notice">Instagram eller Facebook? Prova länken. Om innehållet inte går att läsa kan du klistra in recepttexten nedan. Video och bilder tolkas inte i denna version.</p><details id="paste"><summary>Klistra in recepttext</summary><label for="raw">Bildtext, recepttext eller anteckningar</label><textarea id="raw" rows="7" placeholder="Receptets namn&#10;Ingredienser&#10;2 dl …&#10;Gör så här&#10;Blanda …"></textarea><button id="parse" type="button">Fördela texten</button><p class="notice">Känner igen rubrikerna Ingredienser och Gör så här/Instruktioner. Text utan tydliga rubriker behålls för manuell granskning.</p></details><p id="status" role="status" class="status"></p></section>
<section id="review" class="panel" hidden><p class="eyebrow">GRANSKA IMPORTEN</p><h2>Stämmer receptet?</h2><div id="choice-wrap" hidden><label for="choice">Flera recept hittades – välj ett</label><select id="choice"></select></div><p id="method" class="notice"></p><form id="recipe"><label for="title">Namn</label><input id="title" name="title" required maxlength="200"><label for="description">Beskrivning</label><textarea id="description" name="description" rows="3"></textarea><label for="yield">Portioner eller antal enligt originalet</label><input id="yield" name="yield" placeholder="T.ex. 4 portioner eller 12 kakor"><label for="ingredients">Ingredienser – en per rad</label><textarea id="ingredients" name="ingredients" rows="7" required></textarea><label for="steps">Gör så här – ett steg per rad</label><textarea id="steps" name="steps" rows="7" required></textarea><label for="source">Källänk</label><input id="source" name="source" type="url"><label><input id="checked" type="checkbox" required style="width:auto"> Jag har granskat mängder och instruktioner</label><button>Spara testrecept</button></form><p class="notice">Testrecept sparas endast i den här webbläsaren. Familjekonton, bildimport och publiceringskö kopplas på senare. Inget publiceras på axenhorn.se.</p></section>
<section class="saved"><h2>Mina testrecept</h2><p class="notice">Exportera gärna recepten efter testet. Webbläsardata kan rensas.</p><div id="saved"></div><button id="export" class="secondary">Exportera testrecept</button></section></main>
<script>
const nonce=<?php echo $nonce; ?>;
const $=s=>document.querySelector(s),fields=['title','description','yield','ingredients','steps','source'];
const key='rf-import-lab-v1';let candidates=[],method='',recipes=[];
try{recipes=JSON.parse(localStorage.getItem(key)||'[]');if(!Array.isArray(recipes))recipes=[];}catch{}
function status(s){$('#status').textContent=s;}
function show(r){for(const f of fields)$('#'+f).value=r[f]||'';method=r.method||'Manuellt';$('#method').textContent=method;$('#checked').checked=false;$('#review').hidden=false;$('#title').focus();}
function render(){const root=$('#saved');root.replaceChildren();if(!recipes.length){root.textContent='Inga testrecept ännu. Börja med en länk ovan.';return;}recipes.forEach((r,i)=>{const a=document.createElement('article'),h=document.createElement('h3'),p=document.createElement('p'),b=document.createElement('button');h.textContent=r.title;p.textContent=r.method||'Manuellt';b.textContent='Öppna recept';b.className='secondary';b.onclick=()=>{candidates=[];$('#choice-wrap').hidden=true;show(r);};a.append(h,p,b);root.append(a);});}
$('#import-form').onsubmit=async e=>{e.preventDefault();if(!$('#review').hidden&&!confirm('Ersätta den öppna förhandsgranskningen? Sparade testrecept behålls.'))return;$('#fetch').disabled=true;status('Hämtar och läser receptdata…');try{const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),65000);let response;try{response=await fetch(location.pathname+'?rf_import_lab=1',{method:'POST',headers:{'Content-Type':'application/json','X-RF-Nonce':nonce},body:JSON.stringify({url:$('#url').value}),signal:controller.signal});}finally{clearTimeout(timer);}const data=await response.json();if(!response.ok||!data.success)throw Error(typeof data.data==='string'?data.data:'Importen kunde inte slutföras.');candidates=data.data;$('#choice').replaceChildren(...candidates.map((r,i)=>new Option(r.title||'Recept '+(i+1),String(i))));$('#choice-wrap').hidden=candidates.length<2;show(candidates[0]);const r=candidates[0];status('Receptdata hittades. Kontrollera resultatet mot originalet.'+(!r.ingredients||!r.steps?' Viktigt: ingredienser eller steg saknas.':''));}catch(e){status(e.name==='AbortError'?'Hämtningen tog för lång tid. Prova textimport.':e.message);$('#paste').open=true;}finally{$('#fetch').disabled=false;}};
$('#choice').onchange=()=>show(candidates[Number($('#choice').value)]);
$('#manual').onclick=()=>{if(!$('#review').hidden&&!confirm('Börja med ett tomt recept?'))return;$('#choice-wrap').hidden=true;show({source:$('#url').value,method:'Manuellt'});status('Skriv ditt recept i fälten nedan.');};
$('#parse').onclick=()=>{const text=$('#raw').value.trim();if(!text){status('Klistra först in recepttext.');return;}if(!$('#review').hidden&&!confirm('Ersätta förhandsgranskningen med texten?'))return;const lines=text.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);const ing=lines.findIndex(s=>/^(ingredienser|ingredients)\s*:?$/i.test(s));const step=lines.findIndex(s=>/^(gör så här|instruktioner|tillagning|instructions|method)\s*:?$/i.test(s));const structured=ing>0&&step>ing;$('#choice-wrap').hidden=true;show({title:lines[0],description:structured?lines.slice(1,ing).join('\n'):text,ingredients:structured?lines.slice(ing+1,step).join('\n'):'',steps:structured?lines.slice(step+1).join('\n'):'',source:$('#url').value,method:'Inklistrad text · granska manuellt'});status(structured?'Texten har fördelats efter rubrikerna. Kontrollera alla rader.':'Tydliga rubriker saknas. Hela texten finns kvar i beskrivningen; fördela ingredienser och steg manuellt.');};
$('#recipe').onsubmit=e=>{e.preventDefault();const r=Object.fromEntries(new FormData(e.target));if(!r.title.trim()||!r.ingredients.trim()||!r.steps.trim()){status('Namn, ingredienser och steg måste fyllas i.');return;}r.method=method;r.savedAt=new Date().toISOString();const duplicate=recipes.findIndex(x=>x.source&&x.source===r.source&&x.title===r.title);if(duplicate>=0&&!confirm('Receptet finns redan. Ersätta det sparade testreceptet?'))return;const next=[...recipes];if(duplicate>=0)next[duplicate]=r;else next.unshift(r);try{localStorage.setItem(key,JSON.stringify(next));recipes=next;render();$('#review').hidden=true;status('Testreceptet är sparat i den här webbläsaren.');}catch{status('Receptet kunde inte sparas. Webbläsarens lagring är full eller blockerad.');}};
$('#export').onclick=()=>{const blob=new Blob([JSON.stringify({version:1,recipes},null,2)],{type:'application/json'}),url=URL.createObjectURL(blob),a=document.createElement('a');a.href=url;a.download='real-food-importtest.json';a.click();setTimeout(()=>URL.revokeObjectURL(url),1000);};render();
</script></body></html>
<?php exit;
});
