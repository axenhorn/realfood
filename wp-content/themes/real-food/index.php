<?php defined('ABSPATH') || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip" href="#main">Hoppa till innehållet</a>
<header class="site-header">
 <a class="brand" href="<?php echo esc_url(home_url('/')); ?>"><span class="brand-name">Real Food</span><span class="brand-by">BY AXENHORN</span></a>
 <nav aria-label="Huvudmeny"><?php rf_theme_navigation(); ?></nav>
</header>
<main id="main">
<?php if (is_front_page()) : ?>
 <section class="studio-hero">
  <div class="studio-hero-copy"><p class="eyebrow">FRÅN VÅRT KÖK TILL DITT</p><h1>God mat.<br><em>Värd att vänta på.</em></h1><p>Recept för vattenbadet, grillen och röken. Smaker att samlas kring, med tid och omtanke som ingredienser.</p><a class="button" href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Hitta ditt nästa recept <span aria-hidden="true">→</span></a></div>
  <?php $feature = rf_theme_recipe_data(916); if ($feature && $feature['image']) : ?>
  <figure class="studio-hero-image"><a href="<?php echo esc_url(get_permalink(916)); ?>"><?php echo wp_get_attachment_image($feature['image'], 'large', false, array('loading'=>'eager','fetchpriority'=>'high','sizes'=>'(max-width: 760px) 100vw, 620px')); ?></a><figcaption><span>FRÅN RECEPTSAMLINGEN</span><a href="<?php echo esc_url(get_permalink(916)); ?>">Flankstek sous vide <span aria-hidden="true">↗</span></a></figcaption></figure>
  <?php endif; ?>
 </section>
 <section class="studio-find" aria-label="Hitta recept"><?php rf_studio_search(); rf_studio_topics(); ?></section>
 <section class="studio-section"><div class="studio-section-heading"><div><p class="eyebrow">LAGA NÅGOT GOTT</p><h2>Från vår receptbok.</h2></div><a href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Se alla recept <span aria-hidden="true">→</span></a></div><div class="studio-grid"><?php foreach (array(320, 916, 753, 847, 156, 148) as $id) { rf_studio_card($id); } ?></div></section>
 <section class="studio-methods" aria-labelledby="studio-methods-title"><div><p class="eyebrow">LÄR KÄNNA TEKNIKEN</p><h2 id="studio-methods-title">Lite kunskap.<br><em>Mycket smak.</em></h2><p>Hitta rätt metod och låt tiden göra sitt. Börja med vattenbadet eller ta fram grillen.</p></div><div class="studio-method-links"><a href="<?php echo esc_url(rf_theme_page_url('sous-vide')); ?>"><span>01</span><div><h3>Sous vide</h3><p>Jämn tillagning, från första till sista tuggan.</p></div><b aria-hidden="true">↗</b></a><?php $grill = get_term_by('name','Grillat','category'); if ($grill && !is_wp_error($grill)) : ?><a href="<?php echo esc_url(get_term_link($grill)); ?>"><span>02</span><div><h3>Grillat & BBQ</h3><p>Recept med plats för glöd och tålamod.</p></div><b aria-hidden="true">↗</b></a><?php endif; ?><a href="<?php echo esc_url(rf_theme_page_url('sous-vide-temperaturer-och-tider')); ?>"><span>03</span><div><h3>Temperaturer & tider</h3><p>En guide nära till hands vid vattenbadet.</p></div><b aria-hidden="true">↗</b></a></div></section>
<?php elseif (is_page('alla-recept')) : while (have_posts()) : the_post(); ?>
 <section class="studio-collection"><header class="studio-page-heading"><p class="eyebrow">REAL FOODS RECEPTBOK</p><h1>Vad vill du laga?</h1><p>Från långsam tillagning till tillbehören som gör måltiden. Hitta något att laga, dela och återvända till.</p></header><?php rf_studio_search(); rf_studio_topics(); ?><div class="studio-collection-results"><?php the_content(); ?></div></section>
<?php endwhile; ?>
<?php elseif (is_singular()) : while (have_posts()) : the_post(); ?>
 <article <?php post_class('article'); ?>>
 <div class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Hem</a> / <a href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Recept & guider</a></div>
 <header class="article-header"><p class="eyebrow">REAL FOOD BY AXENHORN</p><h1><?php the_title(); ?></h1><?php if (is_page('sous-vide')) : ?><p class="studio-page-intro">Lär känna vattenbadet. Recept, teknik och inspiration för matlagning med tid och precision.</p><?php elseif (is_page('sous-vide-temperaturer-och-tider')) : ?><p class="studio-page-intro">Tid och temperatur nära till hands när du planerar nästa måltid.</p><?php endif; ?></header>
 <?php $rf_recipe = rf_theme_recipe_data(get_the_ID()); rf_theme_recipe_intro(get_the_ID(), $rf_recipe); ?>
 <div class="article-content"><?php the_content(); wp_link_pages(); ?></div>
 <?php if ($rf_recipe) { rf_theme_related_recipes(get_the_ID()); } ?>
 </article>
<?php endwhile; elseif (is_404()) : ?>
 <section class="article"><h1>Sidan kunde inte hittas.</h1><p>Hitta något gott i receptsamlingen.</p><a class="button" href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Alla recept</a></section>
<?php else : ?>
 <section class="archive"><h1><?php if (is_search()) { printf('Sökresultat för: %s', esc_html(get_search_query())); } else { the_archive_title(); } ?></h1>
 <?php rf_studio_search(); rf_studio_topics(); if (!have_posts()) { echo '<p class="studio-empty">Inga träffar den här gången. Prova en annan råvara eller bläddra bland alla recept.</p>'; } ?>
 <div class="archive-grid"><?php while (have_posts()) : the_post(); ?>
 <article><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large', array('loading'=>'lazy')); ?><h2><?php the_title(); ?></h2></a><?php if (function_exists('rf_card_details_html') && rf_card_details(get_the_ID())) { echo rf_card_details_html(get_the_ID()); } else { the_excerpt(); } ?></article>
 <?php endwhile; ?></div><?php if (!have_posts() && !get_query_var('paged')) : ?><p class="screen-reader-text">Slut på resultat.</p><?php endif; the_posts_pagination(); ?></section>
<?php endif; ?>
</main>
<footer class="site-footer"><div><strong>Real Food</strong><p>Matlagning med tid och omtanke.</p></div><a href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Alla recept</a><?php $privacy = get_privacy_policy_url(); if ($privacy) : ?><a href="<?php echo esc_url($privacy); ?>">Integritet</a><?php endif; ?></footer>
<?php wp_footer(); ?></body></html>

