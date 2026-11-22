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
 <section class="hero"><p class="eyebrow">GOD MAT. TID OCH OMTANKE.</p><h1>Från glöd<br>till <em>middag.</em></h1><p>Recept för grillen, röken och vattenbadet. Från vårt kök till ditt.</p><a class="button" href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Utforska recepten</a></section>
 <section class="routes" aria-label="Hitta rätt i köket">
 <a href="<?php echo esc_url(rf_theme_page_url('sous-vide')); ?>"><span>TEKNIKEN</span><h2>Sous vide</h2><p>Lär känna vattenbadet och hitta nästa recept.</p></a>
 <a href="<?php echo esc_url(rf_theme_page_url('sous-vide-temperaturer-och-tider')); ?>"><span>GUIDEN</span><h2>Rätt temperatur.</h2><p>Temperaturer och tider nära till hands.</p></a>
 </section>
 <section class="recipes-section"><div class="section-heading"><p class="eyebrow">FRÅN RECEPTSAMLINGEN</p><h2>Något gott att laga.</h2></div>
 <?php if (shortcode_exists('real_food_recipes')) { echo do_shortcode('[real_food_recipes collection="all"]'); } else { echo '<p><a href="'.esc_url(rf_theme_page_url('alla-recept')).'">Se alla recept</a></p>'; } ?>
 </section>
<?php elseif (is_singular()) : while (have_posts()) : the_post(); ?>
 <article <?php post_class('article'); ?>>
 <div class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Hem</a> / <a href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Recept & guider</a></div>
 <header class="article-header"><p class="eyebrow">REAL FOOD BY AXENHORN</p><h1><?php the_title(); ?></h1></header>
 <div class="article-content"><?php the_content(); wp_link_pages(); ?></div>
 </article>
<?php endwhile; elseif (is_404()) : ?>
 <section class="article"><h1>Sidan kunde inte hittas.</h1><p>Hitta något gott i receptsamlingen.</p><a class="button" href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Alla recept</a></section>
<?php else : ?>
 <section class="archive"><h1><?php if (is_search()) { printf('Sökresultat för: %s', esc_html(get_search_query())); } else { the_archive_title(); } ?></h1>
 <?php get_search_form(); ?>
 <div class="archive-grid"><?php while (have_posts()) : the_post(); ?>
 <article><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large', array('loading'=>'lazy')); ?><h2><?php the_title(); ?></h2></a><?php the_excerpt(); ?></article>
 <?php endwhile; ?></div><?php if (!have_posts() && !get_query_var('paged')) : ?><p class="screen-reader-text">Slut på resultat.</p><?php endif; the_posts_pagination(); ?></section>
<?php endif; ?>
</main>
<footer class="site-footer"><div><strong>Real Food</strong><p>Matlagning med tid och omtanke.</p></div><a href="<?php echo esc_url(rf_theme_page_url('alla-recept')); ?>">Alla recept</a><?php $privacy = get_privacy_policy_url(); if ($privacy) : ?><a href="<?php echo esc_url($privacy); ?>">Integritet</a><?php endif; ?></footer>
<?php wp_footer(); ?></body></html>
