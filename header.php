<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
	<?php
// Яндекс.Метрика
if (get_theme_mod('yandex_metrika_id')) :
?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(<?php echo esc_js(get_theme_mod('yandex_metrika_id')); ?>, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true,
            ecommerce:"dataLayer"
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/<?php echo esc_attr(get_theme_mod('yandex_metrika_id')); ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
<?php endif; ?>

<!-- Яндекс.Вебмастер -->
<?php if (get_theme_mod('yandex_verification')) : ?>
    <meta name="yandex-verification" content="<?php echo esc_attr(get_theme_mod('yandex_verification')); ?>" />
<?php endif; ?>

<!-- Mail.ru Verification -->
<?php if (get_theme_mod('mailru_verification')) : ?>
    <meta name="mailru-verification" content="<?php echo esc_attr(get_theme_mod('mailru_verification')); ?>" />
<?php endif; ?>

<!-- VK Verification -->
<meta property="vk:image" content="<?php echo esc_url(get_site_icon_url()); ?>" />
<meta name="vk:title" content="<?php bloginfo('name'); ?>" />
<meta name="vk:description" content="<?php bloginfo('description'); ?>" />

<!-- Одноклассники -->
<meta property="og:image" content="<?php echo esc_url(get_site_icon_url()); ?>" />
<meta property="og:title" content="<?php bloginfo('name'); ?>" />
<meta property="og:description" content="<?php bloginfo('description'); ?>" />

<!-- Яндекс.Дзен -->
<?php if (get_theme_mod('yandex_zen')) : ?>
    <meta name="zen-verification" content="<?php echo esc_attr(get_theme_mod('yandex_zen')); ?>" />
<?php endif; ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'newscore'); ?></a>
    
    <header id="masthead" class="site-header">
        <div class="header-top">
            <div class="container">
                <?php if (get_theme_mod('logo_upload')) : ?>
                    <div class="site-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <img src="<?php echo esc_url(get_theme_mod('logo_upload')); ?>" alt="<?php bloginfo('name'); ?>">
                        </a>
                    </div>
                <?php else : ?>
                    <div class="site-branding">
                        <h1 class="site-title">
                            <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
                        </h1>
                        <p class="site-description"><?php bloginfo('description'); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="header-ads">
                    <!-- Место для рекламы 728x90 -->
                    <div class="ad-container ad-header">
                        <?php if (is_active_sidebar('header-ad')) : ?>
                            <?php dynamic_sidebar('header-ad'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <nav id="site-navigation" class="main-navigation">
            <div class="container">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="hamburger"></span>
                    <span class="screen-reader-text"><?php esc_html_e('Menu', 'newscore'); ?></span>
                </button>
                
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id' => 'primary-menu',
                    'container_class' => 'primary-menu-container',
                    'fallback_cb' => false,
                ));
                ?>
                
                <div class="header-search">
                    <button class="search-toggle" aria-expanded="false">
                        <span class="screen-reader-text"><?php esc_html_e('Search', 'newscore'); ?></span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <circle cx="9" cy="9" r="7"/>
                            <path d="M15 15l4 4"/>
                        </svg>
                    </button>
                    <div class="search-form-container">
                        <?php get_search_form(); ?>
                    </div>
                </div>
            </div>
        </nav>
        
        <?php if (is_front_page()) : ?>
        <div class="breaking-news">
            <div class="container">
                <span class="breaking-label"><?php esc_html_e('Breaking News', 'newscore'); ?>:</span>
                <div class="ticker-content">
                    <?php
                    $breaking_posts = wp_get_recent_posts(array(
                        'numberposts' => 5,
                        'post_status' => 'publish',
                        'meta_key' => '_breaking_news',
                        'meta_value' => '1'
                    ));
                    
                    if ($breaking_posts) {
                        foreach ($breaking_posts as $post) {
                            echo '<a href="' . get_permalink($post['ID']) . '">' . esc_html($post['post_title']) . '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </header>
    
    <div id="content" class="site-content">
        <div class="container">
            <?php if (!is_front_page()) : ?>
            <div class="breadcrumbs">
                <?php
                if (function_exists('yoast_breadcrumb')) {
                    yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
                } else {
                    // Простая навигация
                    if (!is_front_page()) {
                        echo '<a href="' . home_url() . '">' . __('Home', 'newscore') . '</a> &raquo; ';
                        if (is_single()) {
                            the_category(', ');
                            echo ' &raquo; ';
                            the_title();
                        }
                    }
                }
                ?>
            </div>
            <?php endif; ?>