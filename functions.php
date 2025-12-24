<?php
/**
 * NewsCore - WordPress тема для российского новостного сайта
 * Полная версия с интеграцией Яндекс, российских сервисов и требованиями Роскомнадзора
 * Версия: 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Выход при прямом доступе
}

// ============================================================================
// 1. КОНСТАНТЫ И БАЗОВАЯ КОНФИГУРАЦИЯ
// ============================================================================

define('NEWSCORE_VERSION', '2.0.0');
define('NEWSCORE_DIR', get_template_directory());
define('NEWSCORE_URI', get_template_directory_uri());

// ============================================================================
// 2. БАЗОВАЯ НАСТРОЙКА ТЕМЫ
// ============================================================================

/**
 * Настройка темы при активации
 */
function newscore_setup() {
    // Поддержка возможностей темы
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    add_theme_support('post-formats', array(
        'standard',
        'image',
        'video',
        'gallery',
        'audio'
    ));
    
    add_theme_support('widgets');
    
    // Кастомный логотип
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    add_theme_support('custom-background', array(
        'default-color' => 'ffffff',
    ));
    
    add_theme_support('custom-header', array(
        'default-image' => '',
        'width'         => 1920,
        'height'        => 400,
        'flex-height'   => true,
    ));
    
    add_theme_support('align-wide');
    
    // Размеры изображений
    add_image_size('newscore-large', 1200, 675, true);
    add_image_size('newscore-medium', 600, 338, true);
    add_image_size('newscore-small', 300, 169, true);
    add_image_size('newscore-featured', 800, 450, true);
    
    // Регистрация меню
    register_nav_menus(array(
        'primary' => __('Главное меню', 'newscore'),
        'footer'  => __('Меню в футере', 'newscore'),
        'mobile'  => __('Мобильное меню', 'newscore'),
    ));
    
    // Загрузка текстового домена
    load_theme_textdomain('newscore', get_template_directory() . '/languages');
}

add_action('after_setup_theme', 'newscore_setup');

/**
 * Действия при активации темы
 */
function newscore_theme_activation() {
    // Создание таблицы для согласий
    newscore_create_consents_table();
    
    // Создание обязательных страниц
    newscore_create_required_pages();
    
    // Сброс правил перезаписи
    flush_rewrite_rules();
}

add_action('after_switch_theme', 'newscore_theme_activation');

// ============================================================================
// 3. ПОДКЛЮЧЕНИЕ СТИЛЕЙ И СКРИПТОВ
// ============================================================================

function newscore_enqueue_assets() {
    // Версия для кэширования
    $theme_version = wp_get_theme()->get('Version');
    
    // Основной стиль
    wp_enqueue_style(
        'newscore-style',
        get_stylesheet_uri(),
        array(),
        $theme_version
    );
    
    // Дополнительные стили
    $styles = array(
        'newscore-main'        => '/assets/css/main.css',
        'newscore-responsive'  => '/assets/css/responsive.css',
        'newscore-russian'     => '/assets/css/russian.css',
        'newscore-roskomnadzor'=> '/assets/css/roskomnadzor.css',
    );
    
    foreach ($styles as $handle => $path) {
        $file_path = NEWSCORE_DIR . $path;
        if (file_exists($file_path)) {
            wp_enqueue_style(
                $handle,
                NEWSCORE_URI . $path,
                array(),
                filemtime($file_path)
            );
        }
    }
    
    // Шрифты Google (с проверкой доступности)
    wp_enqueue_style(
        'newscore-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=PT+Serif:wght@400;700&display=swap&subset=cyrillic',
        array(),
        null
    );
    
    // Font Awesome с локальным фолбэком
    $font_awesome_css = NEWSCORE_DIR . '/assets/css/font-awesome.min.css';
    if (file_exists($font_awesome_css)) {
        wp_enqueue_style(
            'font-awesome',
            NEWSCORE_URI . '/assets/css/font-awesome.min.css',
            array(),
            '6.0.0'
        );
    } else {
        wp_enqueue_style(
            'font-awesome-cdn',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
            array(),
            '6.0.0'
        );
    }
    
    // jQuery с локального сервера если CDN недоступен
    wp_deregister_script('jquery');
    wp_register_script(
        'jquery',
        includes_url('/js/jquery/jquery.js'),
        array(),
        '3.6.0',
        true
    );
    wp_enqueue_script('jquery');
    
    // Основной скрипт
    $main_js_path = NEWSCORE_DIR . '/assets/js/main.js';
    if (file_exists($main_js_path)) {
        wp_enqueue_script(
            'newscore-main-js',
            NEWSCORE_URI . '/assets/js/main.js',
            array('jquery'),
            filemtime($main_js_path),
            true
        );
    }
    
    // Скрипты по условиям
    if (is_front_page() || is_home()) {
        $slider_js_path = NEWSCORE_DIR . '/assets/js/slider.js';
        if (file_exists($slider_js_path)) {
            wp_enqueue_script(
                'newscore-slider',
                NEWSCORE_URI . '/assets/js/slider.js',
                array('jquery'),
                filemtime($slider_js_path),
                true
            );
        }
    }
    
    // Скрипты для российских сервисов
    $russian_services_js = NEWSCORE_DIR . '/assets/js/russian-services.js';
    if (file_exists($russian_services_js)) {
        wp_enqueue_script(
            'newscore-russian-services',
            NEWSCORE_URI . '/assets/js/russian-services.js',
            array('jquery'),
            filemtime($russian_services_js),
            true
        );
    }
    
    // Скрипты для Роскомнадзора
    $roskom_js = NEWSCORE_DIR . '/assets/js/roskomnadzor.js';
    if (file_exists($roskom_js)) {
        wp_enqueue_script(
            'newscore-roskomnadzor',
            NEWSCORE_URI . '/assets/js/roskomnadzor.js',
            array('jquery'),
            filemtime($roskom_js),
            true
        );
    }
    
    // Локализация скриптов
    wp_localize_script('newscore-main-js', 'newscore_ajax', array(
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('newscore_nonce'),
        'siteurl'   => home_url('/'),
        'is_mobile' => wp_is_mobile(),
    ));
    
    wp_localize_script('newscore-russian-services', 'newscore_ru', array(
        'yandex_metrika_id' => esc_js(get_theme_mod('yandex_metrika_id', '')),
        'user_region'       => isset($_COOKIE['user_region']) ? sanitize_text_field($_COOKIE['user_region']) : '',
        'ajax_url'          => admin_url('admin-ajax.php'),
        'nonce'             => wp_create_nonce('newscore_ru_nonce'),
        'privacy_page_url'  => esc_url(get_permalink(get_theme_mod('privacy_policy_page'))),
    ));
    
    wp_localize_script('newscore-roskomnadzor', 'newscore_roskom', array(
        'ajax_url'              => admin_url('admin-ajax.php'),
        'nonce'                 => wp_create_nonce('newscore_roskom_nonce'),
        'yandex_metrika_id'     => esc_js(get_theme_mod('yandex_metrika_id', '')),
        'google_analytics_id'   => esc_js(get_theme_mod('google_analytics_id', '')),
        'privacy_version'       => esc_js(get_theme_mod('privacy_version', '1.0')),
        'privacy_page_url'      => esc_url(get_permalink(get_theme_mod('privacy_policy_page'))),
        'age_restriction'       => (bool) get_theme_mod('show_age_restriction', false),
        'cookie_notice'         => (bool) get_theme_mod('show_cookie_notice', true),
    ));
    
    // Комментарии
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // Удаляем лишние скрипты
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}

add_action('wp_enqueue_scripts', 'newscore_enqueue_assets', 10);

/**
 * Оптимизация загрузки скриптов
 */
function newscore_optimize_scripts() {
    // Отложенная загрузка некритичных скриптов
    add_filter('script_loader_tag', function($tag, $handle) {
        $deferred_scripts = array(
            'newscore-russian-services',
            'newscore-roskomnadzor',
            'font-awesome',
            'font-awesome-cdn'
        );
        
        if (in_array($handle, $deferred_scripts, true)) {
            return str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }, 10, 2);
    
    // Удаляем атрибуты type
    add_filter('style_loader_tag', 'newscore_remove_type_attr', 10, 2);
    add_filter('script_loader_tag', 'newscore_remove_type_attr', 10, 2);
}

add_action('wp_enqueue_scripts', 'newscore_optimize_scripts', 99);

function newscore_remove_type_attr($tag, $handle = '') {
    return preg_replace("/\s*type=['\"]text\/(javascript|css)['\"]/", '', $tag);
}

// ============================================================================
// 4. ВИДЖЕТЫ И САЙДБАРЫ
// ============================================================================

function newscore_widgets_init() {
    // Главный сайдбар
    register_sidebar(array(
        'name'          => __('Сайдбар', 'newscore'),
        'id'            => 'sidebar-1',
        'description'   => __('Основной сайдбар', 'newscore'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Сайдбар для новостей
    register_sidebar(array(
        'name'          => __('Сайдбар новостей', 'newscore'),
        'id'            => 'sidebar-news',
        'description'   => __('Сайдбар для страниц новостей', 'newscore'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Виджеты в футере
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar(array(
            'name'          => sprintf(__('Футер %d', 'newscore'), $i),
            'id'            => 'footer-' . $i,
            'description'   => sprintf(__('Зона для виджетов в футере %d', 'newscore'), $i),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        ));
    }
    
    // Виджет для рекламы в шапке
    register_sidebar(array(
        'name'          => __('Реклама в шапке', 'newscore'),
        'id'            => 'header-ad',
        'description'   => __('Зона для рекламного блока в шапке', 'newscore'),
        'before_widget' => '<div id="%1$s" class="header-ad-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="screen-reader-text">',
        'after_title'   => '</span>',
    ));
    
    // Виджет для срочных новостей
    register_sidebar(array(
        'name'          => __('Срочные новости', 'newscore'),
        'id'            => 'breaking-news',
        'description'   => __('Блок для срочных новостей', 'newscore'),
        'before_widget' => '<div id="%1$s" class="breaking-news-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span class="breaking-label">',
        'after_title'   => '</span>',
    ));
}

add_action('widgets_init', 'newscore_widgets_init');

// ============================================================================
// 5. КАСТОМНЫЕ ФУНКЦИИ ТЕМЫ
// ============================================================================

/**
 * Время чтения статьи
 */
function newscore_reading_time($post_id = null) {
    $post_id = $post_id ? intval($post_id) : get_the_ID();
    
    if (!$post_id) {
        return '';
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // 200 слов в минуту
    
    return sprintf(
        _n('%d мин.', '%d мин.', $reading_time, 'newscore'),
        $reading_time
    );
}

/**
 * Форматирование чисел (1K, 1M)
 */
function newscore_format_number($number) {
    $number = intval($number);
    
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1, '.', '') . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 1, '.', '') . 'K';
    }
    
    return $number;
}

/**
 * Безопасная пагинация
 */
function newscore_pagination() {
    global $wp_query;
    
    if (!$wp_query || $wp_query->max_num_pages <= 1) {
        return;
    }
    
    $big = 999999999;
    $current = max(1, get_query_var('paged'));
    
    $pagination = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => $current,
        'total'     => $wp_query->max_num_pages,
        'prev_text' => __('&laquo; Назад', 'newscore'),
        'next_text' => __('Вперед &raquo;', 'newscore'),
        'type'      => 'array',
        'mid_size'  => 2,
        'end_size'  => 1,
    ));
    
    if (!empty($pagination)) {
        echo '<nav class="pagination" aria-label="' . esc_attr__('Навигация по страницам', 'newscore') . '">';
        echo '<ul>';
        foreach ($pagination as $page) {
            $class = strpos($page, 'current') !== false ? ' class="active"' : '';
            echo '<li' . $class . '>' . $page . '</li>';
        }
        echo '</ul>';
        echo '</nav>';
    }
}

/**
 * Безопасные хлебные крошки
 */
function newscore_breadcrumbs() {
    if (is_front_page()) {
        return;
    }
    
    $breadcrumbs = array();
    
    // Главная страница
    $breadcrumbs[] = array(
        'url'   => home_url('/'),
        'title' => __('Главная', 'newscore'),
        'pos'   => 1,
    );
    
    $position = 2;
    
    if (is_category()) {
        $category = get_queried_object();
        if ($category) {
            $breadcrumbs[] = array(
                'url'   => get_category_link($category->term_id),
                'title' => single_cat_title('', false),
                'pos'   => $position,
            );
        }
    } elseif (is_single()) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $category = $categories[0];
            $breadcrumbs[] = array(
                'url'   => get_category_link($category->term_id),
                'title' => esc_html($category->name),
                'pos'   => $position,
            );
            $position++;
        }
        
        $breadcrumbs[] = array(
            'url'   => get_permalink(),
            'title' => get_the_title(),
            'pos'   => $position,
        );
    } elseif (is_page()) {
        $breadcrumbs[] = array(
            'url'   => get_permalink(),
            'title' => get_the_title(),
            'pos'   => $position,
        );
    } elseif (is_search()) {
        $breadcrumbs[] = array(
            'url'   => get_search_link(),
            'title' => sprintf(__('Результаты поиска: %s', 'newscore'), get_search_query()),
            'pos'   => $position,
        );
    } elseif (is_404()) {
        $breadcrumbs[] = array(
            'url'   => '',
            'title' => __('Ошибка 404', 'newscore'),
            'pos'   => $position,
        );
    }
    
    if (empty($breadcrumbs)) {
        return;
    }
    
    echo '<nav class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">';
    
    foreach ($breadcrumbs as $index => $crumb) {
        $is_last = ($index === count($breadcrumbs) - 1);
        
        echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        
        if (!$is_last && !empty($crumb['url'])) {
            echo '<a href="' . esc_url($crumb['url']) . '" itemprop="item">';
        }
        
        echo '<span itemprop="name">' . esc_html($crumb['title']) . '</span>';
        
        if (!$is_last && !empty($crumb['url'])) {
            echo '</a>';
        }
        
        echo '<meta itemprop="position" content="' . intval($crumb['pos']) . '" />';
        echo '</span>';
        
        if (!$is_last) {
            echo '<span class="breadcrumb-separator"> › </span>';
        }
    }
    
    echo '</nav>';
}

/**
 * Кастомные классы для body
 */
function newscore_body_classes($classes) {
    // Платформа
    if (wp_is_mobile()) {
        $classes[] = 'is-mobile';
    }
    
    // Типы страниц
    if (is_single()) {
        $classes[] = 'single-post';
    }
    
    if (is_front_page()) {
        $classes[] = 'front-page';
    }
    
    if (is_home()) {
        $classes[] = 'home-page';
    }
    
    if (is_page()) {
        $classes[] = 'page-' . get_post_field('post_name');
    }
    
    // Особенности
    if (has_post_thumbnail()) {
        $classes[] = 'has-post-thumbnail';
    }
    
    if (get_theme_mod('show_age_restriction', false)) {
        $classes[] = 'age-restricted';
    }
    
    if (is_rtl()) {
        $classes[] = 'rtl';
    }
    
    // Пользовательские настройки
    $classes[] = 'theme-' . sanitize_html_class(wp_get_theme()->get_stylesheet());
    
    return array_unique($classes);
}

add_filter('body_class', 'newscore_body_classes');

// ============================================================================
// 6. БАЗА ДАННЫХ И ХРАНЕНИЕ ДАННЫХ
// ============================================================================

/**
 * Создание таблицы для согласий
 */
function newscore_create_consents_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'newscore_consents';
    $charset_collate = $wpdb->get_charset_collate();
    
    // Проверяем существование таблицы
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_ip varchar(45) NOT NULL,
            user_agent text NOT NULL,
            consent_type varchar(50) NOT NULL,
            consent_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_consent_type (consent_type),
            KEY idx_created_at (created_at),
            KEY idx_user_ip (user_ip(15))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    // Обновляем структуру если нужно
    $columns = $wpdb->get_col("DESCRIBE $table_name");
    
    if (!in_array('updated_at', $columns, true)) {
        $wpdb->query("ALTER TABLE $table_name ADD updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
}

/**
 * Запись согласия в базу данных
 */
function newscore_record_consent($type, $data = array()) {
    global $wpdb;
    
    if (empty($type) || !is_array($data)) {
        return false;
    }
    
    $table_name = $wpdb->prefix . 'newscore_consents';
    
    $consent_data = array(
        'user_ip'      => newscore_get_user_ip(),
        'user_agent'   => isset($_SERVER['HTTP_USER_AGENT']) ? substr(sanitize_textarea_field($_SERVER['HTTP_USER_AGENT']), 0, 500) : '',
        'consent_type' => sanitize_key($type),
        'consent_data' => wp_json_encode($data),
    );
    
    $format = array('%s', '%s', '%s', '%s');
    
    $result = $wpdb->insert($table_name, $consent_data, $format);
    
    return $result !== false ? $wpdb->insert_id : false;
}

/**
 * Получение IP пользователя
 */
function newscore_get_user_ip() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }
    
    // Очистка IP от прокси
    $ip = preg_replace('/[^0-9a-fA-F:., ]/', '', $ip);
    
    return $ip;
}

// ============================================================================
// 7. ИНТЕГРАЦИЯ С ЯНДЕКС И РОССИЙСКИМИ СЕРВИСАМИ
// ============================================================================

/**
 * Яндекс.Метрика
 */
function newscore_yandex_metrika() {
    $metrika_id = get_theme_mod('yandex_metrika_id', '');
    
    if (empty($metrika_id) || current_user_can('manage_options')) {
        return;
    }
    
    ob_start();
    ?>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
        
        ym(<?php echo intval($metrika_id); ?>, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true,
            ecommerce: "dataLayer",
            defer: true
        });
    </script>
    <noscript>
        <div>
            <img src="https://mc.yandex.ru/watch/<?php echo intval($metrika_id); ?>" 
                 style="position:absolute; left:-9999px;" 
                 alt="<?php esc_attr_e('Счетчик Яндекс.Метрики', 'newscore'); ?>" />
        </div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
    <?php
    echo ob_get_clean();
}

add_action('wp_footer', 'newscore_yandex_metrika', 20);

/**
 * Верификационные теги
 */
function newscore_verification_tags() {
    $verifications = array(
        'yandex-verification' => get_theme_mod('yandex_verification', ''),
        'mailru-verification' => get_theme_mod('mailru_verification', ''),
        'google-site-verification' => get_theme_mod('google_verification', ''),
    );
    
    foreach ($verifications as $name => $content) {
        if (!empty($content)) {
            echo '<meta name="' . esc_attr($name) . '" content="' . esc_attr($content) . '" />' . "\n";
        }
    }
}

add_action('wp_head', 'newscore_verification_tags', 1);

/**
 * Яндекс.Новости RSS
 */
function newscore_yandex_news_rss() {
    if (get_theme_mod('enable_yandex_news', false)) {
        add_feed('yandex-news', 'newscore_yandex_news_feed');
    }
}

add_action('init', 'newscore_yandex_news_rss');

function newscore_yandex_news_feed() {
    // Проверяем доступность функции
    if (!function_exists('header')) {
        return;
    }
    
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    
    echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>';
    ?>
    <rss version="2.0" 
         xmlns:yandex="http://news.yandex.ru" 
         xmlns:media="http://search.yahoo.com/mrss/" 
         xmlns:turbo="http://turbo.yandex.ru">
    <channel>
        <title><?php bloginfo_rss('name'); ?></title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <language>ru</language>
        <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
        
        <?php
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'date_query'     => array(
                array(
                    'after' => '24 hours ago'
                )
            )
        );
        
        $query = new WP_Query($args);
        
        while ($query->have_posts()) : $query->the_post();
            $content = get_the_content_feed('rss2');
            $content = strip_tags($content, '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><img><figure><figcaption><blockquote><table><tr><td><th>');
        ?>
        <item turbo="true">
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <author><?php the_author(); ?></author>
            <yandex:full-text><![CDATA[<?php echo $content; ?>]]></yandex:full-text>
            <?php if (has_post_thumbnail()) : ?>
            <enclosure url="<?php echo esc_url(get_the_post_thumbnail_url(null, 'full')); ?>" type="<?php echo esc_attr(get_post_mime_type(get_post_thumbnail_id())); ?>" />
            <?php endif; ?>
            <category><?php 
                $categories = get_the_category();
                if (!empty($categories)) {
                    echo esc_html($categories[0]->name);
                }
            ?></category>
        </item>
        <?php endwhile; wp_reset_postdata(); ?>
    </channel>
    </rss>
    <?php
    exit;
}

/**
 * RSS для Яндекс.Дзен
 */
function newscore_yandex_zen_rss() {
    if (get_theme_mod('enable_yandex_zen', false)) {
        add_feed('yandex-zen', 'newscore_yandex_zen_feed');
    }
}

add_action('init', 'newscore_yandex_zen_rss');

function newscore_yandex_zen_feed() {
    if (!function_exists('header')) {
        return;
    }
    
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    
    echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>';
    ?>
    <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?php bloginfo_rss('name'); ?></title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <language>ru</language>
        <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
        
        <?php
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
        
        $query = new WP_Query($args);
        
        while ($query->have_posts()) : $query->the_post();
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'full') : '';
        ?>
        <item>
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <author><?php the_author(); ?></author>
            <?php if ($image_url) : ?>
            <enclosure url="<?php echo esc_url($image_url); ?>" type="<?php echo esc_attr(get_post_mime_type(get_post_thumbnail_id())); ?>" />
            <?php endif; ?>
            <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
            <content:encoded><![CDATA[<?php echo wpautop(get_the_content_feed('rss2')); ?>]]></content:encoded>
            <category><?php 
                $categories = get_the_category();
                if (!empty($categories)) {
                    echo esc_html($categories[0]->name);
                }
            ?></category>
        </item>
        <?php endwhile; wp_reset_postdata(); ?>
    </channel>
    </rss>
    <?php
    exit;
}

/**
 * Шорткод для Яндекс.Карт
 */
function newscore_yandex_map_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width'   => '100%',
        'height'  => '400px',
        'lat'     => '55.751244',
        'lon'     => '37.618423',
        'zoom'    => '10',
        'title'   => 'Наш офис',
        'hint'    => '',
        'baloon'  => ''
    ), $atts, 'yandex_map');
    
    $map_id = 'yandex-map-' . uniqid();
    
    wp_enqueue_script(
        'yandex-maps',
        'https://api-maps.yandex.ru/2.1/?lang=ru_RU',
        array(),
        null,
        true
    );
    
    wp_add_inline_script('yandex-maps', "
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ymaps !== 'undefined' && document.getElementById('" . esc_js($map_id) . "')) {
                ymaps.ready(function() {
                    var map = new ymaps.Map('" . esc_js($map_id) . "', {
                        center: [" . floatval($atts['lat']) . ", " . floatval($atts['lon']) . "],
                        zoom: " . intval($atts['zoom']) . "
                    });
                    
                    var placemark = new ymaps.Placemark(
                        [" . floatval($atts['lat']) . ", " . floatval($atts['lon']) . "], 
                        {
                            hintContent: '" . esc_js($atts['hint']) . "',
                            balloonContent: '" . esc_js($atts['baloon']) . "'
                        }
                    );
                    
                    map.geoObjects.add(placemark);
                });
            }
        });
    ");
    
    return sprintf(
        '<div id="%s" class="yandex-map" style="width: %s; height: %s;"></div>',
        esc_attr($map_id),
        esc_attr($atts['width']),
        esc_attr($atts['height'])
    );
}

add_shortcode('yandex_map', 'newscore_yandex_map_shortcode');

/**
 * VK Widgets с фолбэком
 */
function newscore_vk_widgets() {
    if (!get_theme_mod('vk_comments_app_id') || !is_single()) {
        return;
    }
    
    $app_id = intval(get_theme_mod('vk_comments_app_id'));
    
    if ($app_id <= 0) {
        return;
    }
    
    echo '<div id="vk_comments" data-post-id="' . esc_attr(get_the_ID()) . '"></div>';
    
    wp_add_inline_script('vk-api', "
        if (typeof VK !== 'undefined') {
            VK.Widgets.Comments('vk_comments', {
                limit: 10,
                attach: false,
                autoPublish: 1,
                pageUrl: '" . esc_js(get_permalink()) . "'
            }, '" . esc_js(get_the_ID()) . "');
        }
    ");
}

add_action('wp_footer', 'newscore_vk_widgets', 30);

function newscore_enqueue_vk_api() {
    $app_id = get_theme_mod('vk_comments_app_id', '');
    
    if (empty($app_id) && !get_theme_mod('vk_like_button', false)) {
        return;
    }
    
    wp_enqueue_script(
        'vk-api',
        'https://vk.com/js/api/openapi.js?169',
        array(),
        null,
        true
    );
    
    wp_add_inline_script('vk-api', "
        if (typeof VK === 'undefined') {
            console.warn('VK API не загрузился');
        } else {
            VK.init({apiId: " . intval($app_id) . ", onlyWidgets: true});
        }
    ");
}

add_action('wp_enqueue_scripts', 'newscore_enqueue_vk_api', 20);

// ============================================================================
// 8. ФУНКЦИИ ДЛЯ РОСКОМНАДЗОРА И СОБЛЮДЕНИЯ ЗАКОНОДАТЕЛЬСТВА
// ============================================================================

/**
 * Cookie уведомление
 */
function newscore_cookie_notice() {
    if (!get_theme_mod('show_cookie_notice', true)) {
        return;
    }
    
    // Проверяем, приняты ли уже cookies
    if (isset($_COOKIE['newscore_cookie_accepted'])) {
        return;
    }
    
    $privacy_page_id = get_theme_mod('privacy_policy_page');
    $cookie_details_page_id = get_theme_mod('cookie_details_page');
    ?>
    
    <div id="roskom-cookie-notice" class="roskom-cookie-notice" role="alert" aria-live="polite">
        <div class="cookie-container">
            <div class="cookie-content">
                <h4><?php echo esc_html(get_theme_mod('cookie_title', 'Использование файлов cookie')); ?></h4>
                <p><?php echo esc_html(get_theme_mod('cookie_text', 'Этот сайт использует файлы cookie для улучшения работы и аналитики. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности и использованием файлов cookie.')); ?></p>
                
                <div class="cookie-links">
                    <?php if ($privacy_page_id) : ?>
                    <a href="<?php echo esc_url(get_permalink($privacy_page_id)); ?>" class="cookie-link" target="_blank">
                        <?php esc_html_e('Политика конфиденциальности', 'newscore'); ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($cookie_details_page_id) : ?>
                    <a href="<?php echo esc_url(get_permalink($cookie_details_page_id)); ?>" class="cookie-link" target="_blank">
                        <?php esc_html_e('Подробнее о cookies', 'newscore'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="cookie-actions">
                <button type="button" class="cookie-btn cookie-reject" aria-label="<?php esc_attr_e('Отклонить cookies', 'newscore'); ?>">
                    <?php esc_html_e('Отклонить', 'newscore'); ?>
                </button>
                <button type="button" class="cookie-btn cookie-accept" aria-label="<?php esc_attr_e('Принять cookies', 'newscore'); ?>">
                    <?php esc_html_e('Принять', 'newscore'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <?php
}

add_action('wp_footer', 'newscore_cookie_notice', 40);

/**
 * Возрастное ограничение 18+
 */
function newscore_age_restriction() {
    if (!get_theme_mod('show_age_restriction', false)) {
        return;
    }
    
    // Проверяем, подтвержден ли возраст
    if (isset($_COOKIE['newscore_age_confirmed'])) {
        return;
    }
    
    // Проверяем, включено ли для всего сайта
    if (!get_theme_mod('age_restriction_sitewide', false)) {
        return;
    }
    
    $months = array(
        '01' => 'Январь', '02' => 'Февраль', '03' => 'Март',
        '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь',
        '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь',
        '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'
    );
    ?>
    
    <div id="roskom-age-gate" class="roskom-age-gate" role="dialog" aria-modal="true" aria-labelledby="age-gate-title">
        <div class="age-gate-content">
            <div class="age-logo">
                <span class="age-18" aria-hidden="true">18+</span>
            </div>
            
            <h2 id="age-gate-title"><?php echo esc_html(get_theme_mod('age_title', 'Внимание! Возрастное ограничение 18+')); ?></h2>
            
            <p><?php echo esc_html(get_theme_mod('age_text', 'Содержимое этого сайта предназначено для лиц, достигших 18 лет. Подтвердите свой возраст для продолжения.')); ?></p>
            
            <form id="age-gate-form" class="age-form" method="post">
                <div class="age-input-group">
                    <label for="age-day">День</label>
                    <select id="age-day" class="age-select" required>
                        <option value="">День</option>
                        <?php for ($i = 1; $i <= 31; $i++) : ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="age-input-group">
                    <label for="age-month">Месяц</label>
                    <select id="age-month" class="age-select" required>
                        <option value="">Месяц</option>
                        <?php foreach ($months as $num => $name) : ?>
                        <option value="<?php echo esc_attr($num); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="age-input-group">
                    <label for="age-year">Год</label>
                    <select id="age-year" class="age-select" required>
                        <option value="">Год</option>
                        <?php
                        $current_year = intval(date('Y'));
                        for ($i = $current_year; $i >= $current_year - 100; $i--) :
                        ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="age-actions">
                    <button type="button" class="age-btn age-exit" onclick="window.location.href='https://www.google.com'">
                        <?php esc_html_e('Выйти с сайта', 'newscore'); ?>
                    </button>
                    <button type="submit" class="age-btn age-confirm">
                        <?php esc_html_e('Мне есть 18 лет', 'newscore'); ?>
                    </button>
                </div>
                
                <p class="age-warning">
                    <?php echo esc_html(get_theme_mod('age_warning', 'Предоставляя недостоверную информацию, вы нарушаете законодательство РФ.')); ?>
                </p>
            </form>
        </div>
    </div>
    
    <?php
}

add_action('wp_body_open', 'newscore_age_restriction', 1);

/**
 * Возрастная маркировка для постов
 */
function newscore_post_age_badge($content) {
    if (!is_single() || !in_the_loop()) {
        return $content;
    }
    
    $post_id = get_the_ID();
    $age_rating = get_post_meta($post_id, '_age_rating', true);
    
    if (empty($age_rating) || $age_rating === '0+') {
        return $content;
    }
    
    $age_text = get_theme_mod('age_' . $age_rating . '_text', 'Материал для лиц старше ' . $age_rating);
    
    $badge = sprintf(
        '<div class="post-age-badge age-%s" role="note" aria-label="%s">
            <span class="age-label">%s</span>
            <span class="age-text">%s</span>
        </div>',
        esc_attr($age_rating),
        esc_attr($age_text),
        esc_html($age_rating),
        esc_html($age_text)
    );
    
    return $badge . $content;
}

add_filter('the_content', 'newscore_post_age_badge', 5);

/**
 * Согласие на обработку ПД в формах
 */
function newscore_personal_data_form($fields) {
    if (!is_singular() || !comments_open()) {
        return $fields;
    }
    
    $consent_text = get_theme_mod(
        'personal_data_text',
        'Нажимая кнопку, я соглашаюсь на обработку моих персональных данных в соответствии с Федеральным законом № 152-ФЗ «О персональных данных» и принимаю условия Пользовательского соглашения'
    );
    
    $privacy_page_id = get_theme_mod('privacy_policy_page');
    $agreement_page_id = get_theme_mod('user_agreement_page');
    
    $links = array();
    if ($privacy_page_id) {
        $links[] = '<a href="' . esc_url(get_permalink($privacy_page_id)) . '" target="_blank">' . esc_html__('Политикой конфиденциальности', 'newscore') . '</a>';
    }
    if ($agreement_page_id) {
        $links[] = '<a href="' . esc_url(get_permalink($agreement_page_id)) . '" target="_blank">' . esc_html__('Пользовательским соглашением', 'newscore') . '</a>';
    }
    
    if (!empty($links)) {
        $consent_text .= ' (' . implode(' и ', $links) . ')';
    }
    
    $fields['comment_form_consent'] = sprintf(
        '<p class="comment-form-consent">
            <input id="personal_data_consent" name="personal_data_consent" type="checkbox" value="yes" required="required">
            <label for="personal_data_consent">%s</label>
        </p>',
        wp_kses_post($consent_text)
    );
    
    return $fields;
}

add_filter('comment_form_default_fields', 'newscore_personal_data_form');

/**
 * Информация о СМИ в футере
 */
function newscore_media_info() {
    if (!get_theme_mod('show_media_info', false)) {
        return;
    }
    
    echo '<div class="media-info" role="contentinfo">';
    
    $registration_number = get_theme_mod('media_registration_number', '');
    $registration_date = get_theme_mod('media_registration_date', '');
    $media_editor = get_theme_mod('media_editor', '');
    $media_email = get_theme_mod('media_email', '');
    
    if (!empty($registration_number)) {
        echo '<div class="media-registration">';
        echo '<strong>' . esc_html__('Свидетельство о регистрации СМИ:', 'newscore') . '</strong> ';
        echo esc_html($registration_number);
        
        if (!empty($registration_date)) {
            echo ' ' . esc_html__('от', 'newscore') . ' ' . esc_html($registration_date);
        }
        
        echo '</div>';
    }
    
    if (!empty($media_editor)) {
        echo '<div class="media-editor">';
        echo '<strong>' . esc_html__('Главный редактор:', 'newscore') . '</strong> ';
        echo esc_html($media_editor);
        echo '</div>';
    }
    
    if (!empty($media_email) && is_email($media_email)) {
        echo '<div class="media-email">';
        echo '<strong>' . esc_html__('Электронная почта редакции:', 'newscore') . '</strong> ';
        echo '<a href="mailto:' . esc_attr($media_email) . '">' . esc_html($media_email) . '</a>';
        echo '</div>';
    }
    
    echo '</div>';
}

add_action('newscore_footer_before_copyright', 'newscore_media_info');

/**
 * Юридическая информация
 */
function newscore_legal_info() {
    if (!get_theme_mod('show_legal_info', false)) {
        return;
    }
    
    $legal_data = array(
        'legal_name'    => get_theme_mod('legal_name', ''),
        'legal_inn'     => get_theme_mod('legal_inn', ''),
        'legal_ogrn'    => get_theme_mod('legal_ogrn', ''),
        'legal_address' => get_theme_mod('legal_address', ''),
        'legal_phone'   => get_theme_mod('legal_phone', ''),
    );
    
    // Проверяем, есть ли хотя бы одно заполненное поле
    $has_data = false;
    foreach ($legal_data as $value) {
        if (!empty($value)) {
            $has_data = true;
            break;
        }
    }
    
    if (!$has_data) {
        return;
    }
    ?>
    
    <div class="legal-info" role="contentinfo">
        <h4><?php esc_html_e('Юридическая информация', 'newscore'); ?></h4>
        <div class="legal-details">
            <?php foreach ($legal_data as $key => $value) : ?>
                <?php if (!empty($value)) : ?>
                    <?php
                    $labels = array(
                        'legal_name'    => __('Название организации:', 'newscore'),
                        'legal_inn'     => __('ИНН:', 'newscore'),
                        'legal_ogrn'    => __('ОГРН:', 'newscore'),
                        'legal_address' => __('Юридический адрес:', 'newscore'),
                        'legal_phone'   => __('Телефон:', 'newscore'),
                    );
                    ?>
                    <p><strong><?php echo esc_html($labels[$key]); ?></strong> <?php echo esc_html($value); ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php
}

add_action('newscore_footer_widgets', 'newscore_legal_info', 5);

// ============================================================================
// 9. МЕТАБОКСЫ ДЛЯ АДМИНКИ
// ============================================================================

/**
 * Метабокс для возрастной маркировки
 */
function newscore_age_rating_meta_box() {
    add_meta_box(
        'age_rating',
        __('Возрастная маркировка', 'newscore'),
        'newscore_age_rating_meta_box_callback',
        'post',
        'side',
        'high'
    );
}

add_action('add_meta_boxes', 'newscore_age_rating_meta_box');

function newscore_age_rating_meta_box_callback($post) {
    wp_nonce_field('newscore_age_rating', 'age_rating_nonce');
    
    $current_rating = get_post_meta($post->ID, '_age_rating', true);
    $ratings = array('0+', '6+', '12+', '16+', '18+');
    ?>
    
    <p>
        <label for="age_rating"><?php esc_html_e('Возрастное ограничение:', 'newscore'); ?></label>
        <select name="age_rating" id="age_rating" style="width:100%;">
            <option value=""><?php esc_html_e('Без ограничений', 'newscore'); ?></option>
            <?php foreach ($ratings as $rating) : ?>
                <option value="<?php echo esc_attr($rating); ?>" <?php selected($current_rating, $rating); ?>>
                    <?php echo esc_html($rating); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    
    <p class="description">
        <?php esc_html_e('Установите возрастное ограничение согласно ФЗ-436 "О защите детей от информации"', 'newscore'); ?>
    </p>
    
    <?php
}

function newscore_save_age_rating($post_id) {
    // Проверяем nonce
    if (!isset($_POST['age_rating_nonce']) || 
        !wp_verify_nonce($_POST['age_rating_nonce'], 'newscore_age_rating')) {
        return;
    }
    
    // Проверяем автосохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Проверяем права
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Сохраняем данные
    if (isset($_POST['age_rating'])) {
        $age_rating = sanitize_text_field($_POST['age_rating']);
        $allowed_ratings = array('', '0+', '6+', '12+', '16+', '18+');
        
        if (in_array($age_rating, $allowed_ratings, true)) {
            update_post_meta($post_id, '_age_rating', $age_rating);
        } else {
            delete_post_meta($post_id, '_age_rating');
        }
    }
}

add_action('save_post', 'newscore_save_age_rating');

// ============================================================================
// 10. AJAX ФУНКЦИИ
// ============================================================================

/**
 * Регистрация всех AJAX обработчиков
 */
function newscore_register_ajax_handlers() {
    // Загрузка новостей
    add_action('wp_ajax_load_more_posts', 'newscore_load_more_posts');
    add_action('wp_ajax_nopriv_load_more_posts', 'newscore_load_more_posts');
    
    // Роскомнадзор обработчики
    add_action('wp_ajax_record_age_consent', 'newscore_record_age_consent');
    add_action('wp_ajax_nopriv_record_age_consent', 'newscore_record_age_consent');
    
    add_action('wp_ajax_record_personal_data_consent', 'newscore_record_personal_data_consent');
    add_action('wp_ajax_nopriv_record_personal_data_consent', 'newscore_record_personal_data_consent');
    
    add_action('wp_ajax_record_consent', 'newscore_record_consent');
    add_action('wp_ajax_nopriv_record_consent', 'newscore_record_consent');
    
    add_action('wp_ajax_request_data_export', 'newscore_request_data_export');
    add_action('wp_ajax_nopriv_request_data_export', 'newscore_request_data_export');
    
    add_action('wp_ajax_withdraw_consent', 'newscore_withdraw_consent');
    add_action('wp_ajax_nopriv_withdraw_consent', 'newscore_withdraw_consent');
}

add_action('init', 'newscore_register_ajax_handlers');

/**
 * Безопасная AJAX подгрузка новостей
 */
function newscore_load_more_posts() {
    // Проверка nonce
    if (!check_ajax_referer('newscore_nonce', 'security', false)) {
        wp_send_json_error(array(
            'message' => __('Ошибка безопасности', 'newscore')
        ));
        wp_die();
    }
    
    // Валидация параметров
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    
    if ($paged < 1) {
        wp_send_json_error(array(
            'message' => __('Неверный номер страницы', 'newscore')
        ));
        wp_die();
    }
    
    // Аргументы запроса
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => get_option('posts_per_page', 10),
        'paged'          => $paged,
        'post_status'    => 'publish',
        'ignore_sticky_posts' => true,
    );
    
    if ($category > 0) {
        $args['cat'] = $category;
    }
    
    // Выполняем запрос
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content', get_post_type());
        }
        
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'html'       => $html,
            'max_pages'  => $query->max_num_pages,
            'has_more'   => $paged < $query->max_num_pages,
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Нет дополнительных записей', 'newscore')
        ));
    }
    
    wp_die();
}

/**
 * Счетчик просмотров (оптимизированный)
 */
function newscore_update_post_views() {
    if (!is_single()) {
        return;
    }
    
    $post_id = get_the_ID();
    
    if (!$post_id) {
        return;
    }
    
    // Используем транзиент для уменьшения нагрузки на БД
    $transient_key = 'post_views_' . $post_id;
    $views_count = get_transient($transient_key);
    
    if ($views_count === false) {
        $views_count = get_post_meta($post_id, 'post_views', true);
        $views_count = $views_count ? intval($views_count) : 0;
        set_transient($transient_key, $views_count, HOUR_IN_SECONDS);
    }
    
    $views_count++;
    
    // Обновляем метаданные и транзиент
    update_post_meta($post_id, 'post_views', $views_count);
    set_transient($transient_key, $views_count, HOUR_IN_SECONDS);
}

add_action('wp_head', 'newscore_update_post_views');

/**
 * AJAX: Запись подтверждения возраста
 */
function newscore_record_age_consent() {
    if (!check_ajax_referer('newscore_roskom_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'newscore')));
        wp_die();
    }
    
    // Валидация данных
    $age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $birth_date = isset($_POST['birth_date']) ? sanitize_text_field($_POST['birth_date']) : '';
    
    // Проверяем возраст
    if ($age < 18) {
        wp_send_json_error(array('message' => __('Вам меньше 18 лет', 'newscore')));
        wp_die();
    }
    
    // Записываем согласие
    $consent_id = newscore_record_consent('age_confirmation', array(
        'age'        => $age,
        'birth_date' => $birth_date,
        'user_ip'    => newscore_get_user_ip(),
        'timestamp'  => current_time('mysql'),
    ));
    
    if ($consent_id) {
        // Устанавливаем cookie на 30 дней
        setcookie('newscore_age_confirmed', '1', time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        
        wp_send_json_success(array(
            'message'    => __('Согласие записано', 'newscore'),
            'consent_id' => $consent_id
        ));
    } else {
        wp_send_json_error(array('message' => __('Ошибка записи согласия', 'newscore')));
    }
    
    wp_die();
}

/**
 * AJAX: Согласие на обработку ПД
 */
function newscore_record_personal_data_consent() {
    if (!check_ajax_referer('newscore_roskom_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'newscore')));
        wp_die();
    }
    
    // Безопасное извлечение данных формы
    $form_data = array();
    
    if (isset($_POST['form_data']) && is_array($_POST['form_data'])) {
        foreach ($_POST['form_data'] as $key => $value) {
            $form_data[sanitize_key($key)] = sanitize_textarea_field($value);
        }
    }
    
    // Записываем согласие
    $consent_id = newscore_record_consent('personal_data', array(
        'form_data'  => $form_data,
        'user_ip'    => newscore_get_user_ip(),
        'timestamp'  => current_time('mysql'),
        'purpose'    => isset($_POST['purpose']) ? sanitize_text_field($_POST['purpose']) : '',
    ));
    
    if ($consent_id) {
        wp_send_json_success(array(
            'message'    => __('Согласие на обработку ПД записано', 'newscore'),
            'consent_id' => $consent_id
        ));
    } else {
        wp_send_json_error(array('message' => __('Ошибка записи согласия', 'newscore')));
    }
    
    wp_die();
}

/**
 * AJAX: Запрос на экспорт данных
 */
function newscore_request_data_export() {
    if (!check_ajax_referer('newscore_roskom_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'newscore')));
        wp_die();
    }
    
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Неверный email адрес', 'newscore')));
        wp_die();
    }
    
    // Генерируем токен для подтверждения
    $token = wp_generate_password(32, false);
    $expires = time() + (24 * HOUR_IN_SECONDS);
    
    // Сохраняем токен в транзиентах
    set_transient('newscore_export_token_' . $token, array(
        'email'   => $email,
        'user_ip' => newscore_get_user_ip(),
    ), $expires - time());
    
    // Отправляем письмо
    $subject = __('Запрос на экспорт персональных данных', 'newscore');
    $message = sprintf(
        __('Для экспорта ваших персональных данных перейдите по ссылке: %s', 'newscore'),
        add_query_arg(array(
            'action' => 'export_personal_data',
            'token'  => $token,
        ), admin_url('admin-ajax.php'))
    );
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    if (wp_mail($email, $subject, $message, $headers)) {
        wp_send_json_success(array(
            'message' => __('Инструкции отправлены на email', 'newscore')
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Ошибка отправки email', 'newscore')
        ));
    }
    
    wp_die();
}

/**
 * AJAX: Отзыв согласия
 */
function newscore_withdraw_consent() {
    if (!check_ajax_referer('newscore_roskom_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'newscore')));
        wp_die();
    }
    
    $consent_type = isset($_POST['consent_type']) ? sanitize_key($_POST['consent_type']) : '';
    
    if (empty($consent_type)) {
        wp_send_json_error(array('message' => __('Не указан тип согласия', 'newscore')));
        wp_die();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'newscore_consents';
    $user_ip = newscore_get_user_ip();
    
    // Обновляем запись в базе данных
    $updated = $wpdb->update(
        $table_name,
        array(
            'consent_data' => wp_json_encode(array('withdrawn' => true, 'withdrawn_at' => current_time('mysql'))),
            'updated_at'   => current_time('mysql'),
        ),
        array(
            'user_ip'      => $user_ip,
            'consent_type' => $consent_type,
        ),
        array('%s', '%s'),
        array('%s', '%s')
    );
    
    if ($updated !== false) {
        // Удаляем соответствующие cookie
        if ($consent_type === 'cookie') {
            setcookie('newscore_cookie_accepted', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        } elseif ($consent_type === 'age_confirmation') {
            setcookie('newscore_age_confirmed', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        }
        
        wp_send_json_success(array(
            'message' => __('Согласие успешно отозвано', 'newscore'),
            'updated' => $updated
        ));
    } else {
        wp_send_json_error(array('message' => __('Ошибка отзыва согласия', 'newscore')));
    }
    
    wp_die();
}

// ============================================================================
// 11. ШОРТКОДЫ
// ============================================================================

/**
 * Шорткод для политики конфиденциальности
 */
function newscore_privacy_policy_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_date'   => true,
        'show_update' => true,
        'class'       => '',
    ), $atts, 'privacy_policy');
    
    ob_start();
    ?>
    
    <div class="privacy-policy-shortcode <?php echo esc_attr($atts['class']); ?>">
        <h3><?php esc_html_e('Политика конфиденциальности', 'newscore'); ?></h3>
        
        <?php if ($atts['show_date'] && get_theme_mod('privacy_policy_date')) : ?>
            <p class="policy-date">
                <?php esc_html_e('Дата вступления в силу:', 'newscore'); ?>
                <?php echo esc_html(get_theme_mod('privacy_policy_date')); ?>
            </p>
        <?php endif; ?>
        
        <div class="policy-sections">
            <section>
                <h4><?php esc_html_e('1. Сбор информации', 'newscore'); ?></h4>
                <p><?php esc_html_e('Мы собираем информацию, которую вы предоставляете при регистрации, подписке или заполнении форм на сайте.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('2. Использование информации', 'newscore'); ?></h4>
                <p><?php esc_html_e('Собранная информация используется для улучшения работы сайта, персонализации контента и отправки уведомлений.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('3. Защита информации', 'newscore'); ?></h4>
                <p><?php esc_html_e('Мы принимаем меры для защиты ваших персональных данных от несанкционированного доступа.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('4. Cookies', 'newscore'); ?></h4>
                <p><?php esc_html_e('Сайт использует файлы cookie для улучшения пользовательского опыта.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('5. Контакты', 'newscore'); ?></h4>
                <p>
                    <?php esc_html_e('По вопросам обработки персональных данных обращайтесь по email:', 'newscore'); ?>
                    <?php echo esc_html(get_theme_mod('privacy_contact_email', get_bloginfo('admin_email'))); ?>
                </p>
            </section>
        </div>
        
        <?php if ($atts['show_update'] && get_theme_mod('privacy_last_update')) : ?>
            <p class="policy-update">
                <?php esc_html_e('Последнее обновление:', 'newscore'); ?>
                <?php echo esc_html(get_theme_mod('privacy_last_update')); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('privacy_policy', 'newscore_privacy_policy_shortcode');

/**
 * Шорткод для пользовательского соглашения
 */
function newscore_user_agreement_shortcode($atts) {
    $atts = shortcode_atts(array(
        'class' => '',
    ), $atts, 'user_agreement');
    
    ob_start();
    ?>
    
    <div class="user-agreement-shortcode <?php echo esc_attr($atts['class']); ?>">
        <h3><?php esc_html_e('Пользовательское соглашение', 'newscore'); ?></h3>
        
        <div class="agreement-sections">
            <section>
                <h4><?php esc_html_e('1. Общие положения', 'newscore'); ?></h4>
                <p><?php esc_html_e('Используя данный сайт, вы соглашаетесь с условиями настоящего соглашения.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('2. Права и обязанности пользователя', 'newscore'); ?></h4>
                <p><?php esc_html_e('Пользователь обязуется не нарушать законодательство РФ при использовании сайта.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('3. Ограничение ответственности', 'newscore'); ?></h4>
                <p><?php esc_html_e('Администрация сайта не несет ответственности за содержание внешних ссылок.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('4. Интеллектуальная собственность', 'newscore'); ?></h4>
                <p><?php esc_html_e('Все материалы сайта защищены законом об авторском праве.', 'newscore'); ?></p>
            </section>
            
            <section>
                <h4><?php esc_html_e('5. Изменение условий', 'newscore'); ?></h4>
                <p><?php esc_html_e('Администрация оставляет за собой право изменять условия соглашения.', 'newscore'); ?></p>
            </section>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('user_agreement', 'newscore_user_agreement_shortcode');

/**
 * Шорткод для российских праздников
 */
function newscore_russian_holiday_shortcode($atts) {
    $atts = shortcode_atts(array(
        'class' => '',
    ), $atts, 'russian_holiday');
    
    $today = date('j-n');
    $holidays = array(
        '1-1'   => 'С Новым годом! 🎄',
        '1-7'   => 'С Рождеством Христовым! ✨',
        '2-23'  => 'С Днем защитника Отечества! 🎖️',
        '3-8'   => 'С Международным женским днем! 💐',
        '5-1'   => 'С Праздником Весны и Труда! 🌸',
        '5-9'   => 'С Днем Победы! 🎖️',
        '6-12'  => 'С Днем России! 🇷🇺',
        '11-4'  => 'С Днем народного единства! 🤝',
    );
    
    if (isset($holidays[$today])) {
        return sprintf(
            '<div class="holiday-banner %s" role="alert">%s</div>',
            esc_attr($atts['class']),
            esc_html($holidays[$today])
        );
    }
    
    return '';
}

add_shortcode('russian_holiday', 'newscore_russian_holiday_shortcode');

// ============================================================================
// 12. КАСТОМАЙЗЕР (НАСТРОЙКИ ТЕМЫ)
// ============================================================================

function newscore_customize_register($wp_customize) {
    // Панель настроек
    $wp_customize->add_panel('newscore_settings', array(
        'title'    => __('Настройки NewsCore', 'newscore'),
        'priority' => 30,
    ));
    
    // ========== Общие настройки ==========
    $wp_customize->add_section('newscore_general', array(
        'title'    => __('Общие настройки', 'newscore'),
        'panel'    => 'newscore_settings',
        'priority' => 10,
    ));
    
    $wp_customize->add_setting('show_breadcrumbs', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_breadcrumbs', array(
        'label'   => __('Показывать хлебные крошки', 'newscore'),
        'section' => 'newscore_general',
        'type'    => 'checkbox',
    ));
    
    // ========== Цвета ==========
    $wp_customize->add_section('newscore_colors', array(
        'title'    => __('Цветовая схема', 'newscore'),
        'panel'    => 'newscore_settings',
        'priority' => 20,
    ));
    
    $wp_customize->add_setting('primary_color', array(
        'default'           => '#0073aa',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label'    => __('Основной цвет', 'newscore'),
        'section'  => 'newscore_colors',
    )));
    
    $wp_customize->add_setting('accent_color', array(
        'default'           => '#f05a28',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', array(
        'label'    => __('Акцентный цвет', 'newscore'),
        'section'  => 'newscore_colors',
    )));
    
    // ========== Яндекс интеграция ==========
    $wp_customize->add_section('newscore_yandex', array(
        'title'    => __('Яндекс интеграция', 'newscore'),
        'panel'    => 'newscore_settings',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('yandex_metrika_id', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ));
    
    $wp_customize->add_control('yandex_metrika_id', array(
        'label'       => __('ID Яндекс.Метрики', 'newscore'),
        'description' => __('Только цифры, без букв и символов', 'newscore'),
        'section'     => 'newscore_yandex',
        'type'        => 'number',
        'input_attrs' => array('min' => 0),
    ));
    
    $wp_customize->add_setting('enable_yandex_news', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('enable_yandex_news', array(
        'label'       => __('Включить RSS для Яндекс.Новостей', 'newscore'),
        'description' => __('Создает специальный RSS-фид для Яндекс.Новостей', 'newscore'),
        'section'     => 'newscore_yandex',
        'type'        => 'checkbox',
    ));
    
    // ========== Роскомнадзор ==========
    $wp_customize->add_section('newscore_roskomnadzor', array(
        'title'    => __('Роскомнадзор', 'newscore'),
        'panel'    => 'newscore_settings',
        'priority' => 40,
    ));
    
    $wp_customize->add_setting('show_cookie_notice', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_cookie_notice', array(
        'label'   => __('Показывать уведомление о cookies', 'newscore'),
        'section' => 'newscore_roskomnadzor',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('privacy_policy_page', array(
        'default'           => '',
        'sanitize_callback' => 'absint',
    ));
    
    $wp_customize->add_control('privacy_policy_page', array(
        'label'       => __('Страница политики конфиденциальности', 'newscore'),
        'description' => __('Выберите страницу с политикой конфиденциальности', 'newscore'),
        'section'     => 'newscore_roskomnadzor',
        'type'        => 'dropdown-pages',
    ));
    
    $wp_customize->add_setting('show_age_restriction', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_age_restriction', array(
        'label'       => __('Включить возрастное ограничение 18+', 'newscore'),
        'description' => __('Показывать возрастной гейт для всего сайта', 'newscore'),
        'section'     => 'newscore_roskomnadzor',
        'type'        => 'checkbox',
    ));
    
    // ========== Футер ==========
    $wp_customize->add_section('newscore_footer', array(
        'title'    => __('Настройки футера', 'newscore'),
        'panel'    => 'newscore_settings',
        'priority' => 50,
    ));
    
    $wp_customize->add_setting('footer_copyright', array(
        'default'           => sprintf(__('&copy; %s %s. Все права защищены.', 'newscore'), date('Y'), get_bloginfo('name')),
        'sanitize_callback' => 'wp_kses_post',
    ));
    
    $wp_customize->add_control('footer_copyright', array(
        'label'       => __('Текст копирайта', 'newscore'),
        'description' => __('Можно использовать HTML теги', 'newscore'),
        'section'     => 'newscore_footer',
        'type'        => 'textarea',
    ));
    
    $wp_customize->add_setting('show_back_to_top', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_back_to_top', array(
        'label'   => __('Показывать кнопку "Наверх"', 'newscore'),
        'section' => 'newscore_footer',
        'type'    => 'checkbox',
    ));
}

add_action('customize_register', 'newscore_customize_register');

/**
 * Динамические стили из кастомера
 */
function newscore_customizer_css() {
    $primary_color = get_theme_mod('primary_color', '#0073aa');
    $accent_color = get_theme_mod('accent_color', '#f05a28');
    
    if (empty($primary_color) && empty($accent_color)) {
        return;
    }
    
    ob_start();
    ?>
    
    <style id="newscore-custom-colors">
        :root {
            <?php if ($primary_color) : ?>
            --primary-color: <?php echo esc_attr($primary_color); ?>;
            --primary-color-dark: <?php echo esc_attr(newscore_adjust_color($primary_color, -20)); ?>;
            --primary-color-light: <?php echo esc_attr(newscore_adjust_color($primary_color, 20)); ?>;
            <?php endif; ?>
            
            <?php if ($accent_color) : ?>
            --accent-color: <?php echo esc_attr($accent_color); ?>;
            --accent-color-dark: <?php echo esc_attr(newscore_adjust_color($accent_color, -20)); ?>;
            --accent-color-light: <?php echo esc_attr(newscore_adjust_color($accent_color, 20)); ?>;
            <?php endif; ?>
        }
        
        <?php if ($primary_color) : ?>
        a,
        .entry-title a:hover,
        .widget a:hover {
            color: var(--primary-color);
        }
        
        .button-primary,
        input[type="submit"],
        button[type="submit"] {
            background-color: var(--primary-color);
        }
        
        .post-category-badge,
        .category-badge {
            background-color: var(--primary-color);
        }
        <?php endif; ?>
        
        <?php if ($accent_color) : ?>
        .breaking-news,
        .highlight {
            background-color: var(--accent-color);
        }
        
        .button-accent {
            background-color: var(--accent-color);
        }
        <?php endif; ?>
    </style>
    
    <?php
    echo ob_get_clean();
}

add_action('wp_head', 'newscore_customizer_css', 99);

/**
 * Функция для регулировки цвета
 */
function newscore_adjust_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . 
               str_repeat(substr($hex, 1, 1), 2) . 
               str_repeat(substr($hex, 2, 1), 2);
    }
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
                 str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
                 str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// ============================================================================
// 13. СОЗДАНИЕ ОБЯЗАТЕЛЬНЫХ СТРАНИЦ
// ============================================================================

/**
 * Создание обязательных страниц при активации темы
 */
function newscore_create_required_pages() {
    // Массив обязательных страниц
    $pages = array(
        'privacy-policy' => array(
            'title'   => 'Политика конфиденциальности',
            'content' => '[privacy_policy]',
            'meta'    => array('_wp_page_template' => 'page-fullwidth.php'),
        ),
        'user-agreement' => array(
            'title'   => 'Пользовательское соглашение',
            'content' => '[user_agreement]',
            'meta'    => array('_wp_page_template' => 'page-fullwidth.php'),
        ),
        'cookie-policy' => array(
            'title'   => 'Использование cookies',
            'content' => '<h2>Использование файлов cookie на сайте</h2>
                        <p>На этом сайте используются файлы cookie для улучшения пользовательского опыта и аналитики.</p>
                        <p>Продолжая использовать сайт, вы соглашаетесь с нашей политикой использования cookies.</p>',
            'meta'    => array('_wp_page_template' => 'page-fullwidth.php'),
        ),
    );
    
    foreach ($pages as $slug => $page_data) {
        // Проверяем существование страницы
        $existing_page = get_page_by_path($slug);
        
        if (!$existing_page) {
            // Создаем новую страницу
            $page_id = wp_insert_post(array(
                'post_title'     => $page_data['title'],
                'post_name'      => $slug,
                'post_content'   => $page_data['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => 1,
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ));
            
            // Добавляем метаданные
            if ($page_id && !is_wp_error($page_id) && isset($page_data['meta'])) {
                foreach ($page_data['meta'] as $meta_key => $meta_value) {
                    update_post_meta($page_id, $meta_key, $meta_value);
                }
            }
            
            // Устанавливаем страницу политики конфиденциальности по умолчанию
            if ($slug === 'privacy-policy' && $page_id) {
                set_theme_mod('privacy_policy_page', $page_id);
            }
        }
    }
}

// ============================================================================
// 14. ОПТИМИЗАЦИЯ И БЕЗОПАСНОСТЬ
// ============================================================================

/**
 * Очистка заголовка
 */
function newscore_clean_head() {
    // Удаляем ненужные ссылки
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'start_post_rel_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'adjacent_posts_rel_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // Удаляем REST API ссылки для неавторизованных пользователей
    if (!is_user_logged_in()) {
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
    }
}

add_action('init', 'newscore_clean_head');

/**
 * Отключение emoji
 */
function newscore_disable_emoji() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}

add_action('init', 'newscore_disable_emoji');

/**
 * Безопасные заголовки
 */
function newscore_security_headers($headers) {
    // X-Frame-Options
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    
    // X-Content-Type-Options
    $headers['X-Content-Type-Options'] = 'nosniff';
    
    // X-XSS-Protection
    $headers['X-XSS-Protection'] = '1; mode=block';
    
    // Referrer-Policy
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    
    // Content-Security-Policy (базовая)
    if (!is_admin()) {
        $csp = array(
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https:",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src 'self' data: https:",
            "font-src 'self' https:",
            "connect-src 'self' https:",
            "frame-src 'self' https:",
            "media-src 'self' https:",
        );
        
        $headers['Content-Security-Policy'] = implode('; ', $csp);
    }
    
    return $headers;
}

add_filter('wp_headers', 'newscore_security_headers');

/**
 * Безопасный редирект
 */
function newscore_safe_redirect($location, $status = 302) {
    if (wp_validate_redirect($location)) {
        wp_safe_redirect($location, $status);
        exit;
    }
}

// ============================================================================
// 15. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================================================

/**
 * Получение первого абзаца текста
 */
function newscore_get_first_paragraph($content, $length = 200) {
    $content = apply_filters('the_content', $content);
    
    // Удаляем короткоды
    $content = strip_shortcodes($content);
    
    // Получаем первый абзац
    $paragraphs = explode('</p>', $content);
    
    if (!empty($paragraphs[0])) {
        $first_paragraph = $paragraphs[0] . '</p>';
        $first_paragraph = strip_tags($first_paragraph, '<strong><em><b><i>');
        
        // Обрезаем если нужно
        if (strlen($first_paragraph) > $length) {
            $first_paragraph = wp_trim_words($first_paragraph, 30, '...');
        }
        
        return $first_paragraph;
    }
    
    // Фолбэк: обрезаем весь текст
    return wp_trim_words(strip_tags($content), 30, '...');
}

/**
 * Проверка популярности поста
 */
function newscore_is_popular_post($post_id = null, $threshold = 1000) {
    $post_id = $post_id ? intval($post_id) : get_the_ID();
    
    if (!$post_id) {
        return false;
    }
    
    $views = get_post_meta($post_id, 'post_views', true);
    $views = $views ? intval($views) : 0;
    
    return $views >= $threshold;
}

/**
 * Получение связанных постов
 */
function newscore_get_related_posts($post_id = null, $count = 3) {
    $post_id = $post_id ? intval($post_id) : get_the_ID();
    
    if (!$post_id) {
        return array();
    }
    
    // Получаем категории текущего поста
    $categories = wp_get_post_categories($post_id, array('fields' => 'ids'));
    
    if (empty($categories)) {
        return array();
    }
    
    $args = array(
        'post_type'           => 'post',
        'posts_per_page'      => intval($count),
        'post__not_in'        => array($post_id),
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'category__in'        => $categories,
        'orderby'             => 'rand',
        'fields'              => 'ids', // Получаем только ID для кэширования
    );
    
    $cache_key = 'related_posts_' . md5(serialize($args));
    $related_ids = get_transient($cache_key);
    
    if (false === $related_ids) {
        $query = new WP_Query($args);
        $related_ids = $query->posts;
        set_transient($cache_key, $related_ids, HOUR_IN_SECONDS);
    }
    
    if (empty($related_ids)) {
        return array();
    }
    
    // Получаем полные объекты постов
    return array_map('get_post', $related_ids);
}

/**
 * Безопасное логирование
 */
function newscore_log($message, $type = 'debug') {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $log_dir = WP_CONTENT_DIR . '/logs';
    
    // Создаем директорию если нужно
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    $log_file = $log_dir . '/newscore-' . date('Y-m-d') . '.log';
    
    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    }
    
    $timestamp = current_time('mysql');
    $entry = "[$timestamp] [$type] $message\n";
    
    // Пишем в файл
    error_log($entry, 3, $log_file);
}

// ============================================================================
// 16. ВИДЖЕТЫ
// ============================================================================

/**
 * Виджет популярных постов
 */
class Newscore_Popular_Posts_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'newscore_popular_posts',
            __('Популярные новости', 'newscore'),
            array(
                'description' => __('Список самых популярных новостей', 'newscore'),
                'classname'   => 'widget_popular_posts',
            )
        );
    }
    
    public function widget($args, $instance) {
        // Извлекаем настройки
        $title = apply_filters('widget_title', 
            empty($instance['title']) ? __('Популярные новости', 'newscore') : $instance['title']
        );
        
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_views = !empty($instance['show_views']);
        $show_date = !empty($instance['show_date']);
        
        // Выводим обертку виджета
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Запрос популярных постов
        $query_args = array(
            'posts_per_page'      => $number,
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
            'meta_key'            => 'post_views',
            'orderby'             => 'meta_value_num',
            'order'               => 'DESC',
        );
        
        $popular_posts = new WP_Query($query_args);
        
        if ($popular_posts->have_posts()) {
            echo '<ul class="popular-posts-list">';
            
            while ($popular_posts->have_posts()) {
                $popular_posts->the_post();
                $post_id = get_the_ID();
                $views = get_post_meta($post_id, 'post_views', true);
                
                echo '<li class="popular-post-item">';
                echo '<a href="' . esc_url(get_permalink()) . '" class="popular-post-link">';
                
                // Заголовок
                echo '<span class="popular-post-title">' . esc_html(get_the_title()) . '</span>';
                
                // Мета информация
                if ($show_date || $show_views) {
                    echo '<span class="popular-post-meta">';
                    
                    if ($show_date) {
                        echo '<span class="post-date">' . esc_html(get_the_date('d.m.Y')) . '</span>';
                    }
                    
                    if ($show_views && $views) {
                        echo '<span class="post-views">';
                        echo newscore_format_number(intval($views));
                        echo ' ' . _n('просмотр', 'просмотров', intval($views), 'newscore');
                        echo '</span>';
                    }
                    
                    echo '</span>';
                }
                
                echo '</a>';
                echo '</li>';
            }
            
            echo '</ul>';
        } else {
            echo '<p class="no-posts">' . esc_html__('Нет популярных постов', 'newscore') . '</p>';
        }
        
        wp_reset_postdata();
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? esc_attr($instance['title']) : '';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_views = !empty($instance['show_views']);
        $show_date = !empty($instance['show_date']);
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php esc_html_e('Заголовок:', 'newscore'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <?php esc_html_e('Количество постов:', 'newscore'); ?>
            </label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>"
                   name="<?php echo $this->get_field_name('number'); ?>"
                   type="number" value="<?php echo esc_attr($number); ?>"
                   min="1" max="20" step="1">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox"
                   id="<?php echo $this->get_field_id('show_date'); ?>"
                   name="<?php echo $this->get_field_name('show_date'); ?>"
                   <?php checked($show_date); ?>>
            <label for="<?php echo $this->get_field_id('show_date'); ?>">
                <?php esc_html_e('Показывать дату', 'newscore'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox"
                   id="<?php echo $this->get_field_id('show_views'); ?>"
                   name="<?php echo $this->get_field_name('show_views'); ?>"
                   <?php checked($show_views); ?>>
            <label for="<?php echo $this->get_field_id('show_views'); ?>">
                <?php esc_html_e('Показывать количество просмотров', 'newscore'); ?>
            </label>
        </p>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['number'] = absint($new_instance['number']);
        $instance['show_date'] = !empty($new_instance['show_date']);
        $instance['show_views'] = !empty($new_instance['show_views']);
        
        return $instance;
    }
}

/**
 * Виджет категорий новостей
 */
class Newscore_Categories_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'newscore_categories',
            __('Категории новостей', 'newscore'),
            array(
                'description' => __('Список категорий с количеством новостей', 'newscore'),
                'classname'   => 'widget_categories',
            )
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', 
            empty($instance['title']) ? __('Категории', 'newscore') : $instance['title']
        );
        
        $count = !empty($instance['count']);
        $hierarchical = !empty($instance['hierarchical']);
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        $cat_args = array(
            'orderby'      => 'name',
            'show_count'   => $count,
            'hierarchical' => $hierarchical,
            'title_li'     => '',
            'echo'         => 0,
            'depth'        => $hierarchical ? 0 : 1,
        );
        
        $categories = wp_list_categories(apply_filters('widget_categories_args', $cat_args));
        
        if ($categories) {
            echo '<ul class="categories-list">' . $categories . '</ul>';
        } else {
            echo '<p class="no-categories">' . esc_html__('Категории не найдены', 'newscore') . '</p>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? esc_attr($instance['title']) : '';
        $count = !empty($instance['count']);
        $hierarchical = !empty($instance['hierarchical']);
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php esc_html_e('Заголовок:', 'newscore'); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox"
                   id="<?php echo $this->get_field_id('count'); ?>"
                   name="<?php echo $this->get_field_name('count'); ?>"
                   <?php checked($count); ?>>
            <label for="<?php echo $this->get_field_id('count'); ?>">
                <?php esc_html_e('Показывать количество', 'newscore'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox"
                   id="<?php echo $this->get_field_id('hierarchical'); ?>"
                   name="<?php echo $this->get_field_name('hierarchical'); ?>"
                   <?php checked($hierarchical); ?>>
            <label for="<?php echo $this->get_field_id('hierarchical'); ?>">
                <?php esc_html_e('Иерархический вид', 'newscore'); ?>
            </label>
        </p>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['count'] = !empty($new_instance['count']);
        $instance['hierarchical'] = !empty($new_instance['hierarchical']);
        
        return $instance;
    }
}

/**
 * Регистрация кастомных виджетов
 */
function newscore_register_widgets() {
    register_widget('Newscore_Popular_Posts_Widget');
    register_widget('Newscore_Categories_Widget');
}

add_action('widgets_init', 'newscore_register_widgets');

// ============================================================================
// 17. ФИНАЛЬНАЯ ИНИЦИАЛИЗАЦИЯ
// ============================================================================

/**
 * Инициализация темы после загрузки
 */
function newscore_final_init() {
    // Добавляем RSS-ленты в заголовок
    newscore_add_rss_feeds();
    
    // Добавляем мета-теги для Роскомнадзора
    newscore_add_roskomnadzor_meta();
    
    // Проверяем и обновляем структуру базы данных если нужно
    if (current_user_can('activate_plugins')) {
        newscore_check_database_structure();
    }
}

add_action('wp', 'newscore_final_init');

/**
 * Добавление RSS-лент в заголовок
 */
function newscore_add_rss_feeds() {
    if (get_theme_mod('enable_yandex_news', false)) {
        printf(
            '<link rel="alternate" type="application/rss+xml" title="%s - Яндекс.Новости" href="%s" />' . "\n",
            esc_attr(get_bloginfo('name')),
            esc_url(home_url('/feed/yandex-news'))
        );
    }
    
    if (get_theme_mod('enable_yandex_zen', false)) {
        printf(
            '<link rel="alternate" type="application/rss+xml" title="%s - Яндекс.Дзен" href="%s" />' . "\n",
            esc_attr(get_bloginfo('name')),
            esc_url(home_url('/feed/yandex-zen'))
        );
    }
    
    // Основная RSS-лента
    printf(
        '<link rel="alternate" type="application/rss+xml" title="%s - RSS 2.0" href="%s" />' . "\n",
        esc_attr(get_bloginfo('name')),
        esc_url(get_bloginfo('rss2_url'))
    );
}

add_action('wp_head', 'newscore_add_rss_feeds', 2);

/**
 * Добавление мета-тегов для Роскомнадзора
 */
function newscore_add_roskomnadzor_meta() {
    $media_registration = get_theme_mod('media_registration_number', '');
    $geo_city = get_theme_mod('geo_city', '');
    $geo_region = get_theme_mod('geo_region', '');
    
    if (!empty($media_registration)) {
        echo '<meta name="media-registration" content="' . esc_attr($media_registration) . '" />' . "\n";
    }
    
    if (!empty($geo_city)) {
        echo '<meta name="geo.placename" content="' . esc_attr($geo_city) . '" />' . "\n";
    }
    
    if (!empty($geo_region)) {
        echo '<meta name="geo.region" content="RU-' . esc_attr($geo_region) . '" />' . "\n";
    }
}

/**
 * Проверка структуры базы данных
 */
function newscore_check_database_structure() {
    global $wpdb;
    
    // Проверяем существование таблицы согласий
    $table_name = $wpdb->prefix . 'newscore_consents';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        newscore_create_consents_table();
    }
}

/**
 * Транслитерация для URL
 */
function newscore_cyrillic_slug($slug, $post_ID, $post_status, $post_type) {
    if (!preg_match('/[А-Яа-яЁё]/u', $slug)) {
        return $slug;
    }
    
    // Таблица транслитерации
    $converter = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    );
    
    $slug = strtr($slug, $converter);
    
    // Заменяем пробелы и недопустимые символы
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    return $slug;
}

add_filter('sanitize_title', 'newscore_cyrillic_slug', 9, 4);

/**
 * Добавление поддержки WebP и SVG
 */
function newscore_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';
    
    return $mimes;
}

add_filter('upload_mimes', 'newscore_mime_types');

/**
 * Добавление кастомных размеров изображений в медиабиблиотеку
 */
function newscore_custom_image_sizes($sizes) {
    return array_merge($sizes, array(
        'newscore-large'   => __('Большой (1200x675)', 'newscore'),
        'newscore-medium'  => __('Средний (600x338)', 'newscore'),
        'newscore-small'   => __('Маленький (300x169)', 'newscore'),
        'newscore-featured'=> __('Избранное (800x450)', 'newscore'),
    ));
}

add_filter('image_size_names_choose', 'newscore_custom_image_sizes');

/**
 * Отключение ненужных REST API endpoints для гостей
 */
function newscore_disable_rest_api($endpoints) {
    if (!is_user_logged_in()) {
        $restricted = array(
            '/wp/v2/users',
            '/wp/v2/users/(?P<id>[\d]+)',
            '/wp/v2/comments',
            '/wp/v2/comments/(?P<id>[\d]+)',
        );
        
        foreach ($restricted as $endpoint) {
            if (isset($endpoints[$endpoint])) {
                unset($endpoints[$endpoint]);
            }
        }
    }
    
    return $endpoints;
}

add_filter('rest_endpoints', 'newscore_disable_rest_api');

// ============================================================================
// КОНЕЦ ФАЙЛА functions.php
// ============================================================================