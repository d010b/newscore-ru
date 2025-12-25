<?php
/**
 * NewsCore Theme - Test Content Import
 * Version: 1.0.0
 */

// Проверка безопасности
if (!defined('ABSPATH')) {
    exit;
}

class NewsCore_Test_Content_Importer {
    
    private $import_data = array();
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_import_test_content', array($this, 'ajax_import_content'));
    }
    
    /**
     * Добавляем пункт в меню админки
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'Импорт тестового контента',
            'Импорт тестового контента',
            'manage_options',
            'newscore-import-test-content',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Подключаем скрипты и стили
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_newscore-import-test-content') {
            return;
        }
        
        wp_enqueue_style(
            'newscore-import-admin',
            get_template_directory_uri() . '/admin/css/import-admin.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'newscore-import-admin',
            get_template_directory_uri() . '/admin/js/import-admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('newscore-import-admin', 'newscore_import', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('newscore_import_nonce'),
            'importing_text' => __('Импорт...', 'newscore'),
            'success_text' => __('Успешно!', 'newscore'),
            'error_text' => __('Ошибка!', 'newscore')
        ));
    }
    
    /**
     * Рендерим страницу админки
     */
    public function render_admin_page() {
        ?>
        <div class="wrap newscore-import-wrapper">
            <h1><?php esc_html_e('Импорт тестового контента', 'newscore'); ?></h1>
            
            <div class="notice notice-info">
                <p><?php esc_html_e('Этот инструмент создаст тестовый контент для проверки работы темы NewsCore.', 'newscore'); ?></p>
            </div>
            
            <div class="import-sections">
                
                <!-- Секция 1: Тестовая новость -->
                <div class="import-section">
                    <h2>1. Тестовая новость</h2>
                    <div class="section-content">
                        <h3>Запуск нового космического телескопа</h3>
                        <p>Новый телескоп "Горизонт-X" позволит изучать далекие галактики...</p>
                        <button class="button button-primary import-btn" data-section="news_post">
                            Создать тестовую новость
                        </button>
                        <div class="import-status" id="status-news_post"></div>
                    </div>
                </div>
                
                <!-- Секция 2: Категории -->
                <div class="import-section">
                    <h2>2. Тестовые категории</h2>
                    <div class="section-content">
                        <ul>
                            <li>Политика</li>
                            <li>Экономика</li>
                            <li>Наука и технологии</li>
                            <li>Спорт</li>
                            <li>Культура</li>
                            <li>Общество</li>
                            <li>Происшествия</li>
                            <li>В мире</li>
                        </ul>
                        <button class="button button-primary import-btn" data-section="categories">
                            Создать категории
                        </button>
                        <div class="import-status" id="status-categories"></div>
                    </div>
                </div>
                
                <!-- Секция 3: Виджеты -->
                <div class="import-section">
                    <h2>3. Тестовые виджеты</h2>
                    <div class="section-content">
                        <p>Боковая панель, футер, специальные виджеты</p>
                        <button class="button button-primary import-btn" data-section="widgets">
                            Настроить виджеты
                        </button>
                        <div class="import-status" id="status-widgets"></div>
                    </div>
                </div>
                
                <!-- Секция 4: Настройки темы -->
                <div class="import-section">
                    <h2>4. Настройки темы</h2>
                    <div class="section-content">
                        <p>Логотип, цвета, меню, настройки главной</p>
                        <button class="button button-primary import-btn" data-section="theme_settings">
                            Применить настройки
                        </button>
                        <div class="import-status" id="status-theme_settings"></div>
                    </div>
                </div>
                
                <!-- Секция 5: Реквизиты -->
                <div class="import-section">
                    <h2>5. Тестовые реквизиты</h2>
                    <div class="section-content">
                        <p>Контактная информация, API ключи, доступы</p>
                        <button class="button button-primary import-btn" data-section="credentials">
                            Сохранить реквизиты
                        </button>
                        <div class="import-status" id="status-credentials"></div>
                    </div>
                </div>
                
                <!-- Секция 6: Новостное агентство -->
                <div class="import-section">
                    <h2>6. Новостное агентство</h2>
                    <div class="section-content">
                        <p>Информация о редакции, контакты, реквизиты</p>
                        <button class="button button-primary import-btn" data-section="news_agency">
                            Создать страницу агентства
                        </button>
                        <div class="import-status" id="status-news_agency"></div>
                    </div>
                </div>
                
                <!-- Секция 7: Все сразу -->
                <div class="import-section full-width">
                    <h2>Импортировать всё</h2>
                    <div class="section-content">
                        <div class="warning notice notice-warning">
                            <p><strong>Внимание:</strong> Это действие создаст весь тестовый контент. Убедитесь, что у вас нет важных данных.</p>
                        </div>
                        <button class="button button-secondary import-all-btn">
                            Импортировать весь контент
                        </button>
                        <div class="import-status" id="status-all"></div>
                    </div>
                </div>
                
            </div>
            
            <div class="import-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"></div>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * AJAX обработчик импорта
     */
    public function ajax_import_content() {
        // Проверка безопасности
        if (!check_ajax_referer('newscore_import_nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
        
        switch ($section) {
            case 'news_post':
                $result = $this->create_test_news_post();
                break;
            case 'categories':
                $result = $this->create_test_categories();
                break;
            case 'widgets':
                $result = $this->setup_test_widgets();
                break;
            case 'theme_settings':
                $result = $this->apply_theme_settings();
                break;
            case 'credentials':
                $result = $this->save_test_credentials();
                break;
            case 'news_agency':
                $result = $this->create_news_agency_page();
                break;
            case 'all':
                $result = $this->import_all_content();
                break;
            default:
                $result = array('success' => false, 'message' => 'Неизвестная секция');
        }
        
        wp_send_json($result);
    }
    
    /**
     * 1. Создание тестовой новости
     */
    private function create_test_news_post() {
        $post_data = array(
            'post_title'    => 'Запуск нового космического телескопа открывает новые горизонты в астрономии',
            'post_content'  => $this->get_news_content(),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'post',
            'post_excerpt'  => 'Новый космический телескоп "Горизонт-X" начал работу, открывая новые возможности для изучения Вселенной.',
            'meta_input'    => array(
                '_breaking_news' => '1',
                '_featured_post' => '1',
                'post_views' => rand(100, 1000)
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => 'Ошибка создания новости: ' . $post_id->get_error_message()
            );
        }
        
        // Добавляем категорию "Наука и технологии"
        $category_id = term_exists('Наука и технологии', 'category');
        if ($category_id) {
            wp_set_post_categories($post_id, array($category_id['term_id']));
        }
        
        // Добавляем теги
        wp_set_post_tags($post_id, 'космос, астрономия, телескоп, исследования, наука', false);
        
        // Добавляем мета-описание для SEO
        update_post_meta($post_id, '_yoast_wpseo_metadesc', 'Новый космический телескоп "Горизонт-X" начал работу. Узнайте о его возможностях и миссии.');
        
        return array(
            'success' => true,
            'message' => 'Тестовая новость создана! ID: ' . $post_id,
            'post_id' => $post_id,
            'edit_link' => get_edit_post_link($post_id),
            'view_link' => get_permalink($post_id)
        );
    }
    
    /**
     * Контент тестовой новости
     */
    private function get_news_content() {
        return <<<CONTENT
<!-- wp:paragraph -->
<p>Сегодня состоялся исторический запуск нового космического телескопа "Горизонт-X", который обещает революционизировать наше понимание Вселенной. Телескоп, разработанный международной командой ученых, оснащен уникальными инструментами для наблюдения далеких галактик.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Ключевые особенности телескопа:</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Сверхчувствительная камера для инфракрасного излучения</li>
<li>Спектрограф высокого разрешения</li>
<li>Система адаптивной оптики для компенсации атмосферных искажений</li>
</ul>
<!-- /wp:list -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
<p>"Этот телескоп позволит нам заглянуть в самые отдаленные уголки Вселенной и, возможно, обнаружить признаки жизни на экзопланетах", - заявил руководитель проекта доктор Мария Смирнова.</p>
</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Первые научные наблюдения запланированы на следующий месяц. Телескоп будет изучать:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Формирование звезд в туманностях</li>
<li>Черные дыры в центрах галактик</li>
<li>Атмосферы экзопланет</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Технические характеристики:</h3>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table">
<table>
<tbody>
<tr>
<td>Диаметр зеркала:</td>
<td>6.5 метров</td>
</tr>
<tr>
<td>Орбита:</td>
<td>геостационарная</td>
</tr>
<tr>
<td>Срок службы:</td>
<td>15 лет</td>
</tr>
<tr>
<td>Стоимость проекта:</td>
<td>2.5 миллиарда долларов</td>
</tr>
</tbody>
</table>
</figure>
<!-- /wp:table -->

<!-- wp:paragraph -->
<p>Эксперты отмечают, что "Горизонт-X" в 100 раз чувствительнее предыдущих телескопов и сможет обнаруживать объекты, свет от которых шел до Земли более 13 миллиардов лет.</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":999,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large">
<img src="https://picsum.photos/1200/675?random=1" alt="Запуск нового телескопа" class="wp-image-999"/>
<figcaption class="wp-element-caption">Запуск телескопа "Горизонт-X" с космодрома</figcaption>
</figure>
<!-- /wp:image -->
CONTENT;
    }
    
    /**
     * 2. Создание тестовых категорий
     */
    private function create_test_categories() {
        $categories = array(
            array(
                'name' => 'Политика',
                'slug' => 'politics',
                'description' => 'Новости внутренней и международной политики',
                'color' => '#FF0000'
            ),
            array(
                'name' => 'Экономика',
                'slug' => 'economy',
                'description' => 'Финансовые новости, биржи, бизнес',
                'color' => '#00AA00'
            ),
            array(
                'name' => 'Наука и технологии',
                'slug' => 'science-tech',
                'description' => 'Открытия, исследования, IT, гаджеты',
                'color' => '#0073AA'
            ),
            array(
                'name' => 'Спорт',
                'slug' => 'sports',
                'description' => 'Спортивные события, результаты матчей',
                'color' => '#FF8800'
            ),
            array(
                'name' => 'Культура',
                'slug' => 'culture',
                'description' => 'Кино, музыка, театр, искусство',
                'color' => '#9900CC'
            ),
            array(
                'name' => 'Общество',
                'slug' => 'society',
                'description' => 'Социальные вопросы, образование, медицина',
                'color' => '#009999'
            ),
            array(
                'name' => 'Происшествия',
                'slug' => 'incidents',
                'description' => 'Чрезвычайные ситуации, криминал',
                'color' => '#CC0000'
            ),
            array(
                'name' => 'В мире',
                'slug' => 'world',
                'description' => 'Международные новости',
                'color' => '#666666'
            )
        );
        
        $created = array();
        $errors = array();
        
        foreach ($categories as $cat) {
            $term = wp_insert_term(
                $cat['name'],
                'category',
                array(
                    'slug' => $cat['slug'],
                    'description' => $cat['description']
                )
            );
            
            if (is_wp_error($term)) {
                $errors[] = $cat['name'] . ': ' . $term->get_error_message();
            } else {
                $created[] = $cat['name'];
                // Сохраняем цвет категории в мета-поле
                add_term_meta($term['term_id'], 'category_color', $cat['color']);
            }
        }
        
        // Создаем подкатегории для Экономики
        $economy_cat = get_term_by('slug', 'economy', 'category');
        if ($economy_cat) {
            $subcategories = array(
                array('Биржи', 'stock-market'),
                array('Банки', 'banking'),
                array('Недвижимость', 'real-estate')
            );
            
            foreach ($subcategories as $subcat) {
                wp_insert_term(
                    $subcat[0],
                    'category',
                    array(
                        'slug' => $subcat[1],
                        'parent' => $economy_cat->term_id
                    )
                );
            }
        }
        
        // Создаем теги
        $tags = array(
            'выборы-2024',
            'кризис',
            'инновации',
            'чемпионат-мира',
            'ковид',
            'экология',
            'санкции',
            'стартап',
            'олимпиада',
            'кибербезопасность'
        );
        
        foreach ($tags as $tag) {
            wp_insert_term($tag, 'post_tag');
        }
        
        $message = 'Создано категорий: ' . count($created) . ', тегов: ' . count($tags);
        if (!empty($errors)) {
            $message .= '. Ошибки: ' . implode(', ', $errors);
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'created' => $created
        );
    }
    
    /**
     * 3. Настройка тестовых виджетов
     */
    private function setup_test_widgets() {
        global $wp_registered_sidebars;
        
        // Сбрасываем все виджеты
        $sidebars_widgets = get_option('sidebars_widgets', array());
        foreach ($sidebars_widgets as $sidebar => $widgets) {
            if (is_array($widgets)) {
                $sidebars_widgets[$sidebar] = array();
            }
        }
        
        // Настраиваем виджеты для sidebar-1
        $search_widget = array(
            'title' => 'Поиск новостей'
        );
        $this->update_widget('search', 'search-1', $search_widget);
        $sidebars_widgets['sidebar-1'][] = 'search-1';
        
        // Виджет категорий
        $categories_widget = array(
            'title' => 'Рубрики',
            'count' => 1,
            'hierarchical' => 1,
            'dropdown' => 0
        );
        $this->update_widget('categories', 'categories-1', $categories_widget);
        $sidebars_widgets['sidebar-1'][] = 'categories-1';
        
        // Последние записи
        $recent_posts_widget = array(
            'title' => 'Свежие публикации',
            'number' => 5,
            'show_date' => true
        );
        $this->update_widget('recent-posts', 'recent-posts-1', $recent_posts_widget);
        $sidebars_widgets['sidebar-1'][] = 'recent-posts-1';
        
        // Архив
        $archive_widget = array(
            'title' => 'Архив новостей',
            'count' => 0,
            'dropdown' => 0
        );
        $this->update_widget('archives', 'archives-1', $archive_widget);
        $sidebars_widgets['sidebar-1'][] = 'archives-1';
        
        // Облако тегов
        $tag_cloud_widget = array(
            'title' => 'Популярные теги',
            'taxonomy' => 'post_tag'
        );
        $this->update_widget('tag_cloud', 'tag_cloud-1', $tag_cloud_widget);
        $sidebars_widgets['sidebar-1'][] = 'tag_cloud-1';
        
        // Сохраняем изменения
        update_option('sidebars_widgets', $sidebars_widgets);
        
        // Создаем тестовое меню для футера
        $this->create_footer_menu();
        
        return array(
            'success' => true,
            'message' => 'Виджеты настроены. Создано 5 виджетов в боковой панели.',
            'widgets_count' => 5
        );
    }
    
    /**
     * Обновление виджета
     */
    private function update_widget($widget_base, $widget_id, $instance) {
        $widget_instances = get_option('widget_' . $widget_base, array());
        $widget_instances['_multiwidget'] = 1;
        $widget_instances[$widget_id] = $instance;
        update_option('widget_' . $widget_base, $widget_instances);
    }
    
    /**
     * Создание меню футера
     */
    private function create_footer_menu() {
        $menu_name = 'Footer Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);
        
        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($menu_name);
            
            // Добавляем пункты меню
            $menu_items = array(
                array('Главная', home_url('/')),
                array('О нас', home_url('/about')),
                array('Контакты', home_url('/contacts')),
                array('Реклама', home_url('/advertising')),
                array('Вакансии', home_url('/careers'))
            );
            
            foreach ($menu_items as $item) {
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => $item[0],
                    'menu-item-url' => $item[1],
                    'menu-item-status' => 'publish'
                ));
            }
            
            // Привязываем меню к области расположения
            $locations = get_theme_mod('nav_menu_locations');
            $locations['footer'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
    
    /**
     * 4. Применение настроек темы
     */
    private function apply_theme_settings() {
        // Настройки цвета
        set_theme_mod('primary_color', '#0073aa');
        set_theme_mod('accent_color', '#f05a28');
        
        // Настройки заголовка
        set_theme_mod('blogname', 'Мировые Новости');
        set_theme_mod('blogdescription', 'Международное информационное агентство');
        
        // Настройки слайдера
        set_theme_mod('slider_type', 'featured');
        set_theme_mod('slider_posts_count', 5);
        set_theme_mod('slider_autoplay', true);
        set_theme_mod('slider_autoplay_speed', 5000);
        
        // Настройки сетки категорий
        set_theme_mod('home_categories_count', 3);
        set_theme_mod('posts_per_category', 4);
        
        // Настройки отображения
        set_theme_mod('show_breadcrumbs', true);
        set_theme_mod('show_breaking_news', true);
        set_theme_mod('show_back_to_top', true);
        
        // Настройки футера
        $copyright_text = sprintf('&copy; %s %s. Все права защищены.', date('Y'), get_bloginfo('name'));
        set_theme_mod('footer_copyright', $copyright_text);
        
        // Социальные сети
        set_theme_mod('facebook_url', 'https://facebook.com/worldnews');
        set_theme_mod('twitter_url', 'https://twitter.com/worldnews');
        set_theme_mod('telegram_url', 'https://t.me/worldnews');
        set_theme_mod('vkontakte_url', 'https://vk.com/worldnews');
        
        // Российские требования
        set_theme_mod('show_cookie_notice', true);
        set_theme_mod('cookie_title', 'Использование файлов cookie');
        set_theme_mod('cookie_text', 'Этот сайт использует файлы cookie для улучшения работы. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности.');
        
        // Создаем главное меню
        $this->create_primary_menu();
        
        return array(
            'success' => true,
            'message' => 'Настройки темы применены. Создано главное меню.',
            'settings_applied' => 12
        );
    }
    
    /**
     * Создание главного меню
     */
    private function create_primary_menu() {
        $menu_name = 'Primary Menu';
        $menu_exists = wp_get_nav_menu_object($menu_name);
        
        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($menu_name);
            
            // Получаем ID категорий
            $categories = array('politics', 'economy', 'science-tech', 'sports', 'culture');
            
            // Добавляем главную страницу
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => 'Главная',
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish'
            ));
            
            // Добавляем категории
            foreach ($categories as $category_slug) {
                $category = get_term_by('slug', $category_slug, 'category');
                if ($category) {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title' => $category->name,
                        'menu-item-url' => get_category_link($category->term_id),
                        'menu-item-status' => 'publish',
                        'menu-item-type' => 'taxonomy',
                        'menu-item-object' => 'category',
                        'menu-item-object-id' => $category->term_id
                    ));
                }
            }
            
            // Привязываем меню к области расположения
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
    
    /**
     * 5. Сохранение тестовых реквизитов
     */
    private function save_test_credentials() {
        $credentials = array(
            // Контактная информация
            'media_editor' => 'Анна Иванова',
            'media_email' => 'contact@worldnews.ru',
            'media_phone' => '+7 (495) 123-45-67',
            'media_address' => 'г. Москва, ул. Новостная, д. 1',
            
            // Реквизиты СМИ
            'media_registration_number' => 'ЭЛ № ФС 77 - 12345',
            'media_registration_date' => date('d.m.Y'),
            
            // API ключи (тестовые)
            'yandex_metrika_id' => '12345678',
            'google_analytics_id' => 'UA-12345678-1',
            'vk_comments_app_id' => '1234567',
            
            // Настройки погоды
            'default_weather_city' => 'Москва',
            
            // Юридическая информация
            'privacy_policy_page' => $this->get_or_create_page_id('Политика конфиденциальности'),
            'user_agreement_page' => $this->get_or_create_page_id('Пользовательское соглашение')
        );
        
        $saved = 0;
        foreach ($credentials as $key => $value) {
            set_theme_mod($key, $value);
            $saved++;
        }
        
        // Создаем опцию с тестовыми API ключами
        $api_keys = array(
            'yandex_weather' => 'test_api_key_123456',
            'openweather' => 'test_openweather_key_789012',
            'exchange_api' => 'test_exchange_key_345678'
        );
        
        update_option('newscore_test_api_keys', $api_keys);
        
        return array(
            'success' => true,
            'message' => 'Сохранено реквизитов: ' . $saved . ' + 3 API ключа',
            'credentials_count' => $saved
        );
    }
    
    /**
     * Получение или создание страницы
     */
    private function get_or_create_page_id($title) {
        $page = get_page_by_title($title);
        
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $this->get_legal_page_content($title),
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            return $page_id;
        }
        
        return $page->ID;
    }
    
    /**
     * Контент для юридических страниц
     */
    private function get_legal_page_content($title) {
        if ($title === 'Политика конфиденциальности') {
            return '<!-- wp:paragraph -->
<p>Настоящая Политика конфиденциальности определяет порядок обработки и защиты информации о пользователях.</p>
<!-- /wp:paragraph -->';
        } else {
            return '<!-- wp:paragraph -->
<p>Настоящее Пользовательское соглашение регулирует отношения между пользователем и администрацией сайта.</p>
<!-- /wp:paragraph -->';
        }
    }
    
    /**
     * 6. Создание страницы новостного агентства
     */
    private function create_news_agency_page() {
        $page_data = array(
            'post_title' => 'О новостном агентстве',
            'post_name' => 'about-agency',
            'post_content' => $this->get_news_agency_content(),
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => 'template-fullwidth.php',
            'meta_input' => array(
                '_wp_page_template' => 'template-fullwidth.php'
            )
        );
        
        $page_id = wp_insert_post($page_data);
        
        if (is_wp_error($page_id)) {
            return array(
                'success' => false,
                'message' => 'Ошибка создания страницы: ' . $page_id->get_error_message()
            );
        }
        
        // Добавляем мета-поля для контактной информации
        update_post_meta($page_id, 'agency_founded', '2005');
        update_post_meta($page_id, 'agency_employees', '150');
        update_post_meta($page_id, 'agency_languages', 'Русский, Английский, Испанский');
        
        return array(
            'success' => true,
            'message' => 'Страница новостного агентства создана!',
            'page_id' => $page_id,
            'edit_link' => get_edit_post_link($page_id),
            'view_link' => get_permalink($page_id)
        );
    }
    
    /**
     * Контент страницы новостного агентства
     */
    private function get_news_agency_content() {
        return <<<CONTENT
<!-- wp:heading -->
<h2>Мировые Новости (WorldNews)</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Международное информационное агентство, специализирующееся на политике, экономике и технологиях.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Контактная информация</h3>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table">
<table>
<tbody>
<tr>
<td>Адрес:</td>
<td>123456, г. Москва, ул. Новостная, д. 1</td>
</tr>
<tr>
<td>Телефон:</td>
<td>+7 (495) 123-45-67</td>
</tr>
<tr>
<td>Email:</td>
<td>contact@worldnews.ru</td>
</tr>
<tr>
<td>Веб-сайт:</td>
<td>https://worldnews.ru</td>
</tr>
</tbody>
</table>
</figure>
<!-- /wp:table -->

<!-- wp:heading {"level":3} -->
<h3>Редакция</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Главный редактор: Анна Иванова</li>
<li>Заместитель главного редактора: Сергей Васильев</li>
<li>Редактор политического отдела: Михаил Соколов</li>
<li>Редактор экономического отдела: Елена Кузнецова</li>
<li>Редактор технологического отдела: Алексей Морозов</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Наша миссия</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Предоставлять точную, оперативную и независимую информацию о ключевых событиях в мире.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>Принципы работы</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul>
<li>Проверка информации из двух независимых источников</li>
<li>Нейтральный тон изложения</li>
<li>Уважение к частной жизни</li>
<li>Разделение фактов и мнений</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>Статистика</h3>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table">
<table>
<tbody>
<tr>
<td>Основано:</td>
<td>2005 год</td>
</tr>
<tr>
<td>Сотрудников:</td>
<td>150</td>
</tr>
<tr>
<td>Языки:</td>
<td>Русский, Английский, Испанский</td>
</tr>
<tr>
<td>Обновления:</td>
<td>24/7</td>
</tr>
</tbody>
</table>
</figure>
<!-- /wp:table -->
CONTENT;
    }
    
    /**
     * 7. Импорт всего контента
     */
    private function import_all_content() {
        $results = array();
        
        // Создаем категории
        $results['categories'] = $this->create_test_categories();
        
        // Создаем тестовые новости (5 штук)
        $news_results = array();
        for ($i = 1; $i <= 5; $i++) {
            $news_results[] = $this->create_test_news_post();
        }
        $results['news_posts'] = $news_results;
        
        // Настраиваем виджеты
        $results['widgets'] = $this->setup_test_widgets();
        
        // Применяем настройки темы
        $results['theme_settings'] = $this->apply_theme_settings();
        
        // Сохраняем реквизиты
        $results['credentials'] = $this->save_test_credentials();
        
        // Создаем страницу агентства
        $results['news_agency'] = $this->create_news_agency_page();
        
        // Создаем дополнительные страницы
        $additional_pages = array('Контакты', 'Реклама', 'Вакансии');
        foreach ($additional_pages as $page_title) {
            wp_insert_post(array(
                'post_title' => $page_title,
                'post_content' => 'Содержимое страницы ' . $page_title,
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
        }
        
        // Устанавливаем статическую главную страницу
        $front_page = get_page_by_title('О новостном агентстве');
        $posts_page = get_page_by_title('Новости');
        
        if ($front_page) {
            update_option('page_on_front', $front_page->ID);
            update_option('show_on_front', 'page');
        }
        
        // Очищаем кэш
        wp_cache_flush();
        
        return array(
            'success' => true,
            'message' => 'Весь тестовый контент успешно создан!',
            'results' => $results,
            'summary' => array(
                'categories' => '8 основных категорий + подкатегории',
                'news_posts' => '5 тестовых новостей',
                'widgets' => '5 виджетов настроено',
                'pages' => '4 страницы создано',
                'menus' => '2 меню создано'
            )
        );
    }
}

// Инициализация класса
function newscore_test_content_importer_init() {
    if (is_admin() && current_user_can('manage_options')) {
        new NewsCore_Test_Content_Importer();
    }
}
add_action('init', 'newscore_test_content_importer_init');

/**
 * Функция для быстрого импорта через WP-CLI
 */
if (defined('WP_CLI') && WP_CLI) {
    class NewsCore_Test_Content_CLI {
        public function import_all() {
            $importer = new NewsCore_Test_Content_Importer();
            
            WP_CLI::line('Начинаем импорт тестового контента...');
            
            // Импортируем все
            $result = $importer->import_all_content();
            
            if ($result['success']) {
                WP_CLI::success('Импорт завершен успешно!');
                WP_CLI::line('Создано:');
                foreach ($result['summary'] as $key => $value) {
                    WP_CLI::line("  - {$key}: {$value}");
                }
            } else {
                WP_CLI::error('Ошибка при импорте: ' . $result['message']);
            }
        }
        
        public function import_section($args) {
            $section = isset($args[0]) ? $args[0] : '';
            $importer = new NewsCore_Test_Content_Importer();
            
            switch ($section) {
                case 'news':
                    $result = $importer->create_test_news_post();
                    break;
                case 'categories':
                    $result = $importer->create_test_categories();
                    break;
                case 'widgets':
                    $result = $importer->setup_test_widgets();
                    break;
                case 'settings':
                    $result = $importer->apply_theme_settings();
                    break;
                default:
                    WP_CLI::error('Неизвестная секция. Доступные: news, categories, widgets, settings');
                    return;
            }
            
            if ($result['success']) {
                WP_CLI::success($result['message']);
            } else {
                WP_CLI::error($result['message']);
            }
        }
    }
    
    WP_CLI::add_command('newscore import', 'NewsCore_Test_Content_CLI');
}