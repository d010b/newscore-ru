<?php
/**
 * NewsCore - WordPress тема для российского новостного сайта
 * Безопасная версия с исправленными уязвимостями
 * Версия: 2.0.1 (без сервисов Яндекса)
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// 1. КОНСТАНТЫ И БАЗОВАЯ КОНФИГУРАЦИЯ
// ============================================================================

define('NEWSCORE_VERSION', '2.0.1');
define('NEWSCORE_DIR', get_template_directory());
define('NEWSCORE_URI', get_template_directory_uri());

// ============================================================================
// 2. ПОДКЛЮЧЕНИЕ ВСПОМОГАТЕЛЬНЫХ ФАЙЛОВ
// ============================================================================

// Подключаем Walker класс для меню
require_once NEWSCORE_DIR . '/inc/Newscore_Walker_Nav_Menu.php';

// Подключаем кастомайзер (УБИРАЕМ ВЫЗОВ ИЗ ЭТОГО ФАЙЛА, так как он уже есть в customizer.php)
// require_once NEWSCORE_DIR . '/inc/customizer.php';

// Подключаем функции Роскомнадзора
require_once NEWSCORE_DIR . '/inc/roskomnadzor.php';

// ============================================================================
// 3. БАЗОВАЯ НАСТРОЙКА ТЕМЫ
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
        'standard', 'image', 'video', 'gallery', 'audio'
    ));
    add_theme_support('widgets');
    
    // Кастомный логотип
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ));
    
    add_theme_support('custom-background', array(
        'default-color' => 'ffffff',
    ));
    
    add_theme_support('custom-header', array(
        'default-image' => '',
        'width' => 1920,
        'height' => 400,
        'flex-height' => true,
    ));
    
    add_theme_support('align-wide');
    
    // Размеры изображений
    add_image_size('newscore-large', 1200, 675, true);
    add_image_size('newscore-medium', 600, 338, true);
    add_image_size('newscore-small', 300, 169, true);
    add_image_size('newscore-featured', 800, 450, true);
    
    // Регистрация меню
    register_nav_menus(array(
        'primary' => esc_html__('Главное меню', 'newscore'),
        'footer' => esc_html__('Меню в футере', 'newscore'),
        'mobile' => esc_html__('Мобильное меню', 'newscore'),
    ));
    
    // Загрузка текстового домена
    load_theme_textdomain('newscore', NEWSCORE_DIR . '/languages');
}
add_action('after_setup_theme', 'newscore_setup');

// ============================================================================
// 4. ПОДКЛЮЧЕНИЕ СТИЛЕЙ И СКРИПТОВ (БЕЗОПАСНАЯ ВЕРСИЯ)
// ============================================================================

function newscore_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    
    // Основной стиль
    wp_enqueue_style(
        'newscore-style',
        get_stylesheet_uri(),
        array(),
        $theme_version
    );
    
    // Дополнительные стили с проверкой существования
    $styles = array(
        'newscore-main' => '/assets/css/main.css',
        'newscore-responsive' => '/assets/css/responsive.css',
        'newscore-roskomnadzor' => '/assets/css/roskomnadzor.css',
        'newscore-russian' => '/assets/css/russian.css'
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
    
    // Шрифты Google с безопасным подключением
    wp_enqueue_style(
        'newscore-fonts',
        add_query_arg(array(
            'family' => 'Roboto:wght@400;500;700;900|PT+Serif:wght@400;700',
            'subset' => 'cyrillic',
            'display' => 'swap'
        ), 'https://fonts.googleapis.com/css2'),
        array(),
        null
    );
    
    // jQuery через WordPress
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
        wp_localize_script('newscore-roskomnadzor', 'newscore_roskom', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('newscore_roskom_nonce'),
            'privacy_page_url' => esc_url(get_permalink(get_theme_mod('privacy_policy_page'))),
        ));
    }
    
    // Слайдер только для главной
    if (is_front_page()) {
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
    
    // Комментарии
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // Локализация для основного скрипта
    wp_localize_script('newscore-main-js', 'newscore_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('newscore_nonce'),
        'siteurl' => esc_url(home_url('/')),
        'is_mobile' => wp_is_mobile(),
    ));
}
add_action('wp_enqueue_scripts', 'newscore_enqueue_assets', 10);

// ============================================================================
// 5. РЕГИСТРАЦИЯ ВИДЖЕТОВ
// ============================================================================

function newscore_widgets_init() {
    register_sidebar(array(
        'name' => esc_html__('Основной сайдбар', 'newscore'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Добавьте виджеты в основной сайдбар.', 'newscore'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    register_sidebar(array(
        'name' => esc_html__('Сайдбар новостей', 'newscore'),
        'id' => 'sidebar-2',
        'description' => esc_html__('Добавьте виджеты в сайдбар для новостных страниц.', 'newscore'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    // Футер виджеты
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar(array(
            'name' => sprintf(esc_html__('Футер %d', 'newscore'), $i),
            'id' => 'footer-' . $i,
            'description' => sprintf(esc_html__('Виджеты для колонки %d в футере.', 'newscore'), $i),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>',
        ));
    }
    
    // Рекламные области
    register_sidebar(array(
        'name' => esc_html__('Реклама в хедере', 'newscore'),
        'id' => 'header-ad',
        'description' => esc_html__('Рекламный блок в шапке сайта.', 'newscore'),
        'before_widget' => '<div id="%1$s" class="ad-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="ad-title">',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'newscore_widgets_init');

// ============================================================================
// 6. ФУНКЦИИ ДЛЯ РОСКОМНАДЗОРА (БЕЗОПАСНЫЕ)
// ============================================================================

/**
 * Cookie уведомление (безопасная версия)
 */
function newscore_cookie_notice() {
    if (get_theme_mod('show_cookie_notice', true) && !isset($_COOKIE['newscore_cookie_accepted'])) {
        ?>
        <div id="roskom-cookie-notice" class="roskom-cookie-notice">
            <div class="cookie-container">
                <div class="cookie-content">
                    <h4><?php echo esc_html(get_theme_mod('cookie_title', 'Использование файлов cookie')); ?></h4>
                    <p><?php echo esc_html(get_theme_mod('cookie_text', 'Этот сайт использует файлы cookie для улучшения работы. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности.')); ?></p>
                    <div class="cookie-links">
                        <?php
                        $privacy_page = get_theme_mod('privacy_policy_page');
                        if ($privacy_page) {
                            echo '<a href="' . esc_url(get_permalink($privacy_page)) . '" class="cookie-link">';
                            esc_html_e('Политика конфиденциальности', 'newscore');
                            echo '</a>';
                        }
                        ?>
                    </div>
                </div>
                <div class="cookie-actions">
                    <button type="button" class="cookie-btn cookie-reject">
                        <?php esc_html_e('Отклонить', 'newscore'); ?>
                    </button>
                    <button type="button" class="cookie-btn cookie-accept">
                        <?php esc_html_e('Принять', 'newscore'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
add_action('wp_footer', 'newscore_cookie_notice');

// ============================================================================
// 7. AJAX ФУНКЦИИ (БЕЗОПАСНЫЕ)
// ============================================================================

/**
 * Регистрация AJAX обработчиков
 */
function newscore_register_ajax_handlers() {
    // Загрузка новостей
    add_action('wp_ajax_load_more_posts', 'newscore_load_more_posts_secure');
    add_action('wp_ajax_nopriv_load_more_posts', 'newscore_load_more_posts_secure');
    
    // Согласия
    add_action('wp_ajax_record_consent', 'newscore_record_consent_secure');
    add_action('wp_ajax_nopriv_record_consent', 'newscore_record_consent_secure');
}
add_action('init', 'newscore_register_ajax_handlers');

/**
 * Безопасная загрузка новостей
 */
function newscore_load_more_posts_secure() {
    // Проверка nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'newscore_nonce')) {
        wp_send_json_error(array(
            'message' => esc_html__('Ошибка безопасности', 'newscore')
        ));
        return;
    }
    
    // Валидация параметров
    $paged = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
    
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => get_option('posts_per_page', 10),
        'paged' => $paged,
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
    );
    
    if ($category > 0) {
        $args['cat'] = $category;
    }
    
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
            'html' => $html,
            'max_pages' => $query->max_num_pages,
            'has_more' => $paged < $query->max_num_pages,
        ));
    } else {
        wp_send_json_error(array(
            'message' => esc_html__('Нет дополнительных новостей', 'newscore')
        ));
    }
    
    wp_die();
}

/**
 * Безопасное согласие
 */
function newscore_record_consent_secure() {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'newscore_roskom_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }
    
    $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : '';
    $decision = isset($_POST['decision']) ? sanitize_key($_POST['decision']) : '';
    
    if (empty($type) || empty($decision)) {
        wp_send_json_error(array('message' => 'Missing data'));
        return;
    }
    
    // Здесь должна быть логика сохранения согласия
    // Например, в базе данных или логах
    
    wp_send_json_success(array(
        'message' => esc_html__('Согласие записано', 'newscore')
    ));
    
    wp_die();
}

// ============================================================================
// 8. ОПТИМИЗАЦИЯ И БЕЗОПАСНОСТЬ
// ============================================================================

/**
 * Заголовки безопасности
 */
function newscore_security_headers($headers) {
    if (!headers_sent()) {
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    }
    return $headers;
}
add_filter('wp_headers', 'newscore_security_headers');

/**
 * Защита от SQL инъекций в поиске
 */
function newscore_sanitize_search_query($query) {
    if (is_search() && !empty($query->query_vars['s'])) {
        $query->query_vars['s'] = sanitize_text_field($query->query_vars['s']);
    }
    return $query;
}
add_filter('pre_get_posts', 'newscore_sanitize_search_query');

/**
 * Отключение XML-RPC для безопасности
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Скрытие версии WordPress
 */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

// Исправление для strip_tags в WordPress
add_filter('admin_title', function($admin_title) {
    return $admin_title ? (string) $admin_title : '';
}, 1);


// ============================================================================
// 9. ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================================================

/**
 * Расчет времени чтения
 */
function newscore_reading_time() {
    $content = get_post_field('post_content', get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    
    if ($reading_time < 1) {
        return esc_html__('Менее 1 минуты', 'newscore');
    } else {
        return sprintf(
            esc_html(_n('%d минута', '%d минут', $reading_time, 'newscore')),
            $reading_time
        );
    }
}

/**
 * Форматирование чисел
 */
function newscore_format_number($number) {
    $number = intval($number);
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return $number;
}

/**
 * Хлебные крошки
 */
function newscore_breadcrumbs() {
    if (is_front_page()) {
        return;
    }
    
    echo '<div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">';
    
    echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" itemprop="item"><span itemprop="name">' . esc_html__('Главная', 'newscore') . '</span></a>';
    echo '<meta itemprop="position" content="1" />';
    echo '</span>';
    
    if (is_category()) {
        $cat = get_queried_object();
        echo ' <span class="separator">›</span> ';
        echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span itemprop="name">' . esc_html($cat->name) . '</span>';
        echo '<meta itemprop="position" content="2" />';
        echo '</span>';
    } elseif (is_single()) {
        $categories = get_the_category();
        if (!empty($categories)) {
            $category = $categories[0];
            echo ' <span class="separator">›</span> ';
            echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" itemprop="item"><span itemprop="name">' . esc_html($category->name) . '</span></a>';
            echo '<meta itemprop="position" content="2" />';
            echo '</span>';
        }
        
        echo ' <span class="separator">›</span> ';
        echo '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span itemprop="name">' . esc_html(get_the_title()) . '</span>';
        echo '<meta itemprop="position" content="3" />';
        echo '</span>';
    }
    
    echo '</div>';
}

/**
 * Пагинация
 */
function newscore_pagination() {
    global $wp_query;
    $big = 999999999;
    
    $pages = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'type' => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'mid_size' => 2,
        'end_size' => 1,
    ));
    
    if (is_array($pages)) {
        echo '<div class="pagination">';
        foreach ($pages as $page) {
            echo '<span class="page-item">' . $page . '</span>';
        }
        echo '</div>';
    }
}

// ============================================================================
// 10. ИНТЕГРАЦИЯ С РОССИЙСКИМИ СЕРВИСАМИ
// ============================================================================

/**
 * Российские социальные сети
 */
function newscore_russian_social_integration() {
    // VK интеграция (если настроена)
    $vk_app_id = get_theme_mod('vk_comments_app_id', '');
    if ($vk_app_id && is_single()) {
        echo '<script src="https://vk.com/js/api/openapi.js?169" async></script>';
        echo '<script>window.vkAsyncInit = function() { VK.init({ apiId: ' . absint($vk_app_id) . ', onlyWidgets: true }); };</script>';
    }
}
add_action('wp_footer', 'newscore_russian_social_integration');

// Подключаем кастомайзер после определения всех функций
require_once NEWSCORE_DIR . '/inc/customizer.php';

// ============================================================================
// ЗАВЕРШЕНИЕ ФАЙЛА
// ============================================================================
/**
 * Подключение системы импорта тестового контента
 */
function newscore_load_test_content_importer() {
    // Подключаем только в админке
    if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
        // Основной файл импорта
        require_once get_template_directory() . '/import-test-content.php';
        
        // Шорткоды для фронтенда
        if (!is_admin()) {
            require_once get_template_directory() . '/shortcode-import.php';
        }
    }
    
    // Установочный скрипт
    if (isset($_GET['setup_test_content']) && $_GET['setup_test_content'] === '1') {
        require_once get_template_directory() . '/setup-test-content.php';
    }
}
add_action('after_setup_theme', 'newscore_load_test_content_importer');

/**
 * Добавляем пункт в административную панель
 */
function newscore_add_import_admin_bar($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node(array(
        'id'    => 'newscore-import',
        'title' => 'Импорт тестового контента',
        'href'  => admin_url('tools.php?page=newscore-import-test-content'),
        'meta'  => array(
            'title' => 'Импортировать тестовый контент для NewsCore',
            'class' => 'newscore-import-toolbar'
        )
    ));
}
add_action('admin_bar_menu', 'newscore_add_import_admin_bar', 100);

/**
 * CSS для админ-бара
 */
function newscore_import_admin_bar_css() {
    if (!is_admin_bar_showing()) {
        return;
    }
    ?>
    <style>
    #wp-admin-bar-newscore-import .ab-item {
        color: #f05a28 !important;
    }
    #wp-admin-bar-newscore-import:hover .ab-item {
        color: #fff !important;
        background: #0073aa;
    }
    </style>
    <?php
}
add_action('wp_head', 'newscore_import_admin_bar_css');
add_action('admin_head', 'newscore_import_admin_bar_css');
?>