<?php
/**
 * Russian SEO optimizations
 */

// Яндекс.Справочник (Organization markup)
function newscore_yandex_organization_markup() {
    if (!get_theme_mod('enable_yandex_directory')) return;
    
    $organization_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'logo' => get_site_icon_url(),
        'description' => get_bloginfo('description'),
        'address' => array(
            '@type' => 'PostalAddress',
            'addressCountry' => 'RU',
            'addressLocality' => get_theme_mod('org_city', 'Москва'),
            'postalCode' => get_theme_mod('org_zip', ''),
            'streetAddress' => get_theme_mod('org_address', '')
        ),
        'contactPoint' => array(
            '@type' => 'ContactPoint',
            'telephone' => get_theme_mod('org_phone', ''),
            'contactType' => 'customer service',
            'availableLanguage' => 'Russian'
        )
    );
    
    // Добавляем соцсети
    $same_as = array();
    if (get_theme_mod('vk_url')) $same_as[] = get_theme_mod('vk_url');
    if (get_theme_mod('telegram_url')) $same_as[] = get_theme_mod('telegram_url');
    if (get_theme_mod('ok_url')) $same_as[] = get_theme_mod('ok_url');
    
    if (!empty($same_as)) {
        $organization_data['sameAs'] = $same_as;
    }
    
    echo '<script type="application/ld+json">' . wp_json_encode($organization_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
add_action('wp_head', 'newscore_yandex_organization_markup');

// Биржевой прокол (для Яндекса)
function newscore_yandex_stock_protocol() {
    if (!get_theme_mod('enable_stock_protocol')) return;
    
    $current_time = current_time('timestamp');
    $last_modified = get_lastpostmodified('GMT');
    
    echo '<meta name="yandex" content="' . date('YmdHis', strtotime($last_modified)) . '">';
    
    // Для главной страницы
    if (is_front_page()) {
        echo '<meta name="yandex" content="index, follow">';
        echo '<meta name="googlebot" content="index, follow">';
    }
}
add_action('wp_head', 'newscore_yandex_stock_protocol');

// Микроразметка для новостей (NewsArticle)
function newscore_newsarticle_markup() {
    if (!is_single()) return;
    
    global $post;
    
    $article_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => get_the_title(),
        'description' => get_the_excerpt(),
        'datePublished' => get_the_date('c'),
        'dateModified' => get_the_modified_date('c'),
        'author' => array(
            '@type' => 'Person',
            'name' => get_the_author()
        ),
        'publisher' => array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_site_icon_url()
            )
        ),
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id' => get_permalink()
        )
    );
    
    // Изображение
    if (has_post_thumbnail()) {
        $image_url = get_the_post_thumbnail_url(null, 'full');
        $article_data['image'] = array(
            '@type' => 'ImageObject',
            'url' => $image_url,
            'width' => 1200,
            'height' => 675
        );
    }
    
    // Ключевые слова (теги)
    $tags = get_the_tags();
    if ($tags) {
        $keywords = array();
        foreach ($tags as $tag) {
            $keywords[] = $tag->name;
        }
        $article_data['keywords'] = implode(', ', $keywords);
    }
    
    // Раздел (категория)
    $categories = get_the_category();
    if (!empty($categories)) {
        $article_data['articleSection'] = $categories[0]->name;
    }
    
    echo '<script type="application/ld+json">' . wp_json_encode($article_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
add_action('wp_head', 'newscore_newsarticle_markup');

// Оптимизация для Рунета
function newscore_runet_optimizations() {
    // Убираем эмодзи для старых браузеров
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Отключаем лишние скрипты
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
    
    // Оптимизация для Яндекса
    add_filter('wp_headers', function($headers) {
        $headers['X-Yandex-Pumpkin-Seed'] = '1';
        return $headers;
    });
}
add_action('init', 'newscore_runet_optimizations');

// Региональная привязка (геотаргетинг)
function newscore_geo_targeting() {
    $region = get_theme_mod('geo_region', 'RU');
    $city = get_theme_mod('geo_city', '');
    
    if ($city) {
        echo '<meta name="geo.region" content="' . esc_attr($region) . '-' . esc_attr($city) . '">';
        echo '<meta name="geo.placename" content="' . esc_attr($city) . '">';
    }
    
    echo '<meta name="geo.position" content="' . esc_attr(get_theme_mod('geo_lat', '55.751244')) . ';' . esc_attr(get_theme_mod('geo_lon', '37.618423')) . '">';
    echo '<meta name="ICBM" content="' . esc_attr(get_theme_mod('geo_lat', '55.751244')) . ', ' . esc_attr(get_theme_mod('geo_lon', '37.618423')) . '">';
}
add_action('wp_head', 'newscore_geo_targeting');

// Хлебные крошки для Яндекса
function newscore_yandex_breadcrumbs() {
    if (!is_single() && !is_page()) return;
    
    $breadcrumbs = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );
    
    $position = 1;
    
    // Главная страница
    $breadcrumbs['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $position,
        'name' => 'Главная',
        'item' => home_url('/')
    );
    
    $position++;
    
    // Для записей
    if (is_single()) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $category = $categories[0];
            $breadcrumbs['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $category->name,
                'item' => get_category_link($category->term_id)
            );
            $position++;
        }
        
        $breadcrumbs['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => get_the_title(),
            'item' => get_permalink()
        );
    }
    
    echo '<script type="application/ld+json">' . wp_json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
add_action('wp_head', 'newscore_yandex_breadcrumbs');