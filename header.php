<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    
    <?php
    // Яндекс.Метрика
    if (get_theme_mod('yandex_metrika_id')) :
    ?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
        
        ym(<?php echo absint(get_theme_mod('yandex_metrika_id')); ?>, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true,
            defer: true
        });
    </script>
    <noscript>
        <div style="position:absolute;left:-9999px;">
            <img src="https://mc.yandex.ru/watch/<?php echo absint(get_theme_mod('yandex_metrika_id')); ?>" 
                 alt="<?php esc_attr_e('Счетчик Яндекс.Метрики', 'newscore'); ?>" />
        </div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
    <?php endif; ?>
    
    <!-- Open Graph метатеги -->
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="<?php echo (is_single() || is_page()) ? 'article' : 'website'; ?>" />
    <meta property="og:title" content="<?php 
        if (is_single() || is_page()) {
            echo esc_attr(wp_strip_all_tags(get_the_title()));
        } else {
            echo esc_attr(get_bloginfo('name'));
        }
    ?>" />
    <meta property="og:description" content="<?php 
        if (is_single() || is_page()) {
            echo esc_attr(wp_trim_words(get_the_excerpt(), 20, '...'));
        } else {
            echo esc_attr(get_bloginfo('description'));
        }
    ?>" />
    <meta property="og:url" content="<?php echo esc_url(home_url($_SERVER['REQUEST_URI'])); ?>" />
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
    
    <?php if (is_single() && has_post_thumbnail()) : ?>
    <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <?php else : ?>
    <meta property="og:image" content="<?php echo esc_url(get_theme_mod('default_og_image', get_template_directory_uri() . '/assets/images/og-default.jpg')); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <?php endif; ?>
    
    <!-- VK Open Graph -->
    <meta property="vk:image" content="<?php 
        if (is_single() && has_post_thumbnail()) {
            echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large'));
        } else {
            echo esc_url(get_theme_mod('default_social_image', get_template_directory_uri() . '/assets/images/social-default.jpg'));
        }
    ?>" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php 
        if (is_single() || is_page()) {
            echo esc_attr(wp_strip_all_tags(get_the_title()));
        } else {
            echo esc_attr(get_bloginfo('name'));
        }
    ?>" />
    <meta name="twitter:description" content="<?php 
        if (is_single() || is_page()) {
            echo esc_attr(wp_trim_words(get_the_excerpt(), 20, '...'));
        } else {
            echo esc_attr(get_bloginfo('description'));
        }
    ?>" />
    <?php if (is_single() && has_post_thumbnail()) : ?>
    <meta name="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>" />
    <?php endif; ?>
    
    <!-- Верификационные метатеги -->
    <?php if (get_theme_mod('yandex_verification')) : ?>
    <meta name="yandex-verification" content="<?php echo esc_attr(get_theme_mod('yandex_verification')); ?>" />
    <?php endif; ?>
    
    <?php if (get_theme_mod('mailru_verification')) : ?>
    <meta name="mailru-verification" content="<?php echo esc_attr(get_theme_mod('mailru_verification')); ?>" />
    <?php endif; ?>
    
    <?php if (get_theme_mod('google_verification')) : ?>
    <meta name="google-site-verification" content="<?php echo esc_attr(get_theme_mod('google_verification')); ?>" />
    <?php endif; ?>
    
    <!-- Яндекс.Дзен -->
    <?php if (get_theme_mod('yandex_zen_id')) : ?>
    <meta name="zen-verification" content="<?php echo esc_attr(get_theme_mod('yandex_zen_id')); ?>" />
    <?php endif; ?>
    
    <!-- Канонический URL -->
    <link rel="canonical" href="<?php echo esc_url(home_url($_SERVER['REQUEST_URI'])); ?>" />
    
    <!-- RSS ленты -->
    <link rel="alternate" type="application/rss+xml" title="<?php echo esc_attr(get_bloginfo('name')); ?> RSS" href="<?php echo esc_url(get_bloginfo('rss2_url')); ?>" />
    
    <?php if (get_theme_mod('enable_yandex_news', false)) : ?>
    <link rel="alternate" type="application/rss+xml" title="<?php echo esc_attr(get_bloginfo('name')); ?> для Яндекс.Новостей" href="<?php echo esc_url(home_url('/feed/yandex-news')); ?>" />
    <?php endif; ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'newscore'); ?>
    </a>
    
    <header id="masthead" class="site-header" role="banner">
        <div class="header-top">
            <div class="container">
                <?php if (get_theme_mod('logo_upload')) : ?>
                    <div class="site-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                            <img src="<?php echo esc_url(get_theme_mod('logo_upload')); ?>" 
                                 alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                                 width="200" height="60">
                        </a>
                    </div>
                <?php else : ?>
                    <div class="site-branding">
                        <h1 class="site-title">
                            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                                <?php bloginfo('name'); ?>
                            </a>
                        </h1>
                        <?php if (get_bloginfo('description')) : ?>
                        <p class="site-description"><?php bloginfo('description'); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="header-ads" aria-label="Реклама">
                    <div class="ad-container ad-header">
                        <?php if (is_active_sidebar('header-ad')) : ?>
                            <?php dynamic_sidebar('header-ad'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Главное меню">
            <div class="container">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="Открыть меню">
                    <span class="hamburger" aria-hidden="true"></span>
                    <span class="menu-text"><?php esc_html_e('Menu', 'newscore'); ?></span>
                </button>
                
                <?php
                wp_nav_menu(array(
                    'theme_location'  => 'primary',
                    'menu_id'         => 'primary-menu',
                    'container_class' => 'primary-menu-container',
                    'fallback_cb'     => false,
                    'depth'           => 3,
                    'walker'          => new Newscore_Walker_Nav_Menu(),
                ));
                ?>
                
                <div class="header-search" role="search">
                    <button class="search-toggle" aria-expanded="false" aria-label="Открыть поиск">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true">
                            <circle cx="9" cy="9" r="7"/>
                            <path d="M15 15l4 4"/>
                        </svg>
                        <span class="screen-reader-text"><?php esc_html_e('Search', 'newscore'); ?></span>
                    </button>
                    <div class="search-form-container" aria-hidden="true">
                        <?php get_search_form(); ?>
                    </div>
                </div>
            </div>
        </nav>
        
        <?php if (is_front_page() && get_theme_mod('show_breaking_news', true)) : ?>
        <div class="breaking-news" role="region" aria-label="Срочные новости">
            <div class="container">
                <span class="breaking-label"><?php esc_html_e('Breaking News', 'newscore'); ?>:</span>
                <div class="ticker-content" aria-live="polite">
                    <?php
                    $breaking_posts = new WP_Query(array(
                        'posts_per_page' => 5,
                        'post_status'    => 'publish',
                        'meta_key'       => '_breaking_news',
                        'meta_value'     => '1',
                        'no_found_rows'  => true,
                    ));
                    
                    if ($breaking_posts->have_posts()) {
                        while ($breaking_posts->have_posts()) {
                            $breaking_posts->the_post();
                            echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<span class="no-breaking-news">' . esc_html__('Нет срочных новостей', 'newscore') . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </header>
    
    <div id="content" class="site-content">
        <div class="container">
            <?php if (!is_front_page() && get_theme_mod('show_breadcrumbs', true)) : ?>
            <nav class="breadcrumbs" aria-label="Хлебные крошки">
                <?php
                if (function_exists('yoast_breadcrumb')) {
                    yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
                } else {
                    newscore_breadcrumbs();
                }
                ?>
            </nav>
            <?php endif; ?>