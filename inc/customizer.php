<?php
/**
 * Theme Customizer
 */

function newscore_customizer_settings($wp_customize) {
    // Главные настройки
    $wp_customize->add_panel('newscore_settings', array(
        'title' => __('NewsCore Settings', 'newscore'),
        'priority' => 30,
    ));
    
    // Секция: Общие настройки
    $wp_customize->add_section('newscore_general', array(
        'title' => __('General Settings', 'newscore'),
        'panel' => 'newscore_settings',
        'priority' => 10,
    ));
    
    // Показывать хлебные крошки
    $wp_customize->add_setting('show_breadcrumbs', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_breadcrumbs', array(
        'label' => __('Show Breadcrumbs', 'newscore'),
        'section' => 'newscore_general',
        'type' => 'checkbox',
    ));
    
    // Секция: Заголовок
    $wp_customize->add_section('newscore_header', array(
        'title' => __('Header Settings', 'newscore'),
        'panel' => 'newscore_settings',
        'priority' => 20,
    ));
    
    // Показывать срочные новости
    $wp_customize->add_setting('show_breaking_news', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_breaking_news', array(
        'label' => __('Show Breaking News Ticker', 'newscore'),
        'section' => 'newscore_header',
        'type' => 'checkbox',
    ));
    
    // Текст срочных новостей
    $wp_customize->add_setting('breaking_news_text', array(
        'default' => __('Breaking News', 'newscore'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('breaking_news_text', array(
        'label' => __('Breaking News Label', 'newscore'),
        'section' => 'newscore_header',
        'type' => 'text',
    ));
    
    // Секция: Цвета
    $wp_customize->add_section('newscore_colors', array(
        'title' => __('Color Scheme', 'newscore'),
        'panel' => 'newscore_settings',
        'priority' => 30,
    ));
    
    // Основной цвет
    $wp_customize->add_setting('primary_color', array(
        'default' => '#0073aa',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label' => __('Primary Color', 'newscore'),
        'section' => 'newscore_colors',
        'settings' => 'primary_color',
    )));
    
    // Акцентный цвет
    $wp_customize->add_setting('accent_color', array(
        'default' => '#f05a28',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', array(
        'label' => __('Accent Color', 'newscore'),
        'section' => 'newscore_colors',
        'settings' => 'accent_color',
    )));
    
    // Секция: Социальные сети
    $wp_customize->add_section('newscore_social', array(
        'title' => __('Social Media', 'newscore'),
        'panel' => 'newscore_settings',
        'priority' => 40,
    ));
    
    $social_platforms = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'telegram' => 'Telegram',
        'vkontakte' => 'VKontakte',
        'odnoklassniki' => 'Odnoklassniki',
    );
    
    foreach ($social_platforms as $platform => $label) {
        $wp_customize->add_setting($platform . '_url', array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        
        $wp_customize->add_control($platform . '_url', array(
            'label' => sprintf(__('%s URL', 'newscore'), $label),
            'section' => 'newscore_social',
            'type' => 'url',
        ));
    }
    
    // Секция: Футер
    $wp_customize->add_section('newscore_footer', array(
        'title' => __('Footer Settings', 'newscore'),
        'panel' => 'newscore_settings',
        'priority' => 50,
    ));
    
    // Копирайт
    $wp_customize->add_setting('footer_copyright', array(
        'default' => sprintf(__('&copy; %s %s. All rights reserved.', 'newscore'), date('Y'), get_bloginfo('name')),
        'sanitize_callback' => 'wp_kses_post',
    ));
    
    $wp_customize->add_control('footer_copyright', array(
        'label' => __('Copyright Text', 'newscore'),
        'section' => 'newscore_footer',
        'type' => 'textarea',
    ));
    
    // Показывать кнопку "Наверх"
    $wp_customize->add_setting('show_back_to_top', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('show_back_to_top', array(
        'label' => __('Show Back to Top Button', 'newscore'),
        'section' => 'newscore_footer',
        'type' => 'checkbox',
    ));

 // Добавить в существующий customizer.php

// Секция: Яндекс
$wp_customize->add_section('newscore_yandex', array(
    'title' => __('Яндекс интеграция', 'newscore'),
    'panel' => 'newscore_settings',
    'priority' => 25,
));

// Яндекс.Метрика ID
$wp_customize->add_setting('yandex_metrika_id', array(
    'default' => '',
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control('yandex_metrika_id', array(
    'label' => __('ID Яндекс.Метрики', 'newscore'),
    'description' => __('Введите номер счетчика Яндекс.Метрики', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'number',
));

// Яндекс.Вебмастер
$wp_customize->add_setting('yandex_verification', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('yandex_verification', array(
    'label' => __('Код подтверждения Яндекс.Вебмастер', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'text',
));

// Яндекс.Дзен
$wp_customize->add_setting('yandex_zen', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('yandex_zen', array(
    'label' => __('Код подтверждения Яндекс.Дзен', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'text',
));

// Включить Яндекс.Новости
$wp_customize->add_setting('enable_yandex_news', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_yandex_news', array(
    'label' => __('Включить RSS для Яндекс.Новостей', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'checkbox',
));

// Категория для Яндекс.Новостей
$wp_customize->add_setting('yandex_news_category', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('yandex_news_category', array(
    'label' => __('Категория для Яндекс.Новостей', 'newscore'),
    'description' => __('Например: Политика, Общество, Экономика', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'text',
));

// Включить Турбо-страницы
$wp_customize->add_setting('enable_yandex_turbo', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_yandex_turbo', array(
    'label' => __('Включить Турбо-страницы', 'newscore'),
    'section' => 'newscore_yandex',
    'type' => 'checkbox',
));

// Секция: Российские соцсети
$wp_customize->add_section('newscore_russian_social', array(
    'title' => __('Российские соцсети', 'newscore'),
    'panel' => 'newscore_settings',
    'priority' => 26,
));

// VK App ID для комментариев
$wp_customize->add_setting('vk_comments_app_id', array(
    'default' => '',
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control('vk_comments_app_id', array(
    'label' => __('VK App ID для комментариев', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'number',
));

// Кнопка "Мне нравится" VK
$wp_customize->add_setting('vk_like_button', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('vk_like_button', array(
    'label' => __('Включить кнопку "Мне нравится" VK', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'checkbox',
));

// Виджет Одноклассников
$wp_customize->add_setting('ok_widget', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('ok_widget', array(
    'label' => __('Включить виджет Одноклассников', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'checkbox',
));

// ID группы Одноклассников
$wp_customize->add_setting('ok_widget_group_id', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('ok_widget_group_id', array(
    'label' => __('ID группы Одноклассников', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'text',
));

// Telegram Share Button
$wp_customize->add_setting('telegram_share', array(
    'default' => true,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('telegram_share', array(
    'label' => __('Кнопка "Поделиться в Telegram"', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'checkbox',
));

// Яндекс.Дзен виджет
$wp_customize->add_setting('enable_dzen_embed', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_dzen_embed', array(
    'label' => __('Встроить виджет Яндекс.Дзен', 'newscore'),
    'section' => 'newscore_russian_social',
    'type' => 'checkbox',
));

// Секция: Российское SEO
$wp_customize->add_section('newscore_russian_seo', array(
    'title' => __('Российское SEO', 'newscore'),
    'panel' => 'newscore_settings',
    'priority' => 27,
));

// Включить Яндекс.Справочник
$wp_customize->add_setting('enable_yandex_directory', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_yandex_directory', array(
    'label' => __('Включить Яндекс.Справочник', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'checkbox',
));

// Геотаргетинг
$wp_customize->add_setting('geo_region', array(
    'default' => 'RU',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('geo_region', array(
    'label' => __('Регион (код страны)', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

$wp_customize->add_setting('geo_city', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('geo_city', array(
    'label' => __('Город', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

// Координаты
$wp_customize->add_setting('geo_lat', array(
    'default' => '55.751244',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('geo_lat', array(
    'label' => __('Широта', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

$wp_customize->add_setting('geo_lon', array(
    'default' => '37.618423',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('geo_lon', array(
    'label' => __('Долгота', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

// Биржевой протокол
$wp_customize->add_setting('enable_stock_protocol', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_stock_protocol', array(
    'label' => __('Включить биржевой протокол', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'checkbox',
));

// Организация
$wp_customize->add_setting('org_city', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('org_city', array(
    'label' => __('Город организации', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

$wp_customize->add_setting('org_address', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('org_address', array(
    'label' => __('Адрес организации', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

$wp_customize->add_setting('org_phone', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('org_phone', array(
    'label' => __('Телефон организации', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));

// Mail.ru Verification
$wp_customize->add_setting('mailru_verification', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('mailru_verification', array(
    'label' => __('Код подтверждения Mail.ru', 'newscore'),
    'section' => 'newscore_russian_seo',
    'type' => 'text',
));
// Добавить в существующий customizer.php

// Секция: Роскомнадзор и законодательство РФ
$wp_customize->add_section('newscore_roskomnadzor', array(
    'title' => __('Роскомнадзор и законодательство', 'newscore'),
    'panel' => 'newscore_settings',
    'priority' => 28,
));

// Cookie уведомление
$wp_customize->add_setting('show_cookie_notice', array(
    'default' => true,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('show_cookie_notice', array(
    'label' => __('Показывать уведомление о cookies', 'newscore'),
    'description' => __('Требование ФЗ-152 о персональных данных', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('cookie_title', array(
    'default' => 'Использование файлов cookie',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('cookie_title', array(
    'label' => __('Заголовок уведомления о cookies', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('cookie_text', array(
    'default' => 'Этот сайт использует файлы cookie для улучшения работы и аналитики. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности и использованием файлов cookie.',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('cookie_text', array(
    'label' => __('Текст уведомления о cookies', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

$wp_customize->add_setting('cookie_details_page', array(
    'default' => '',
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control('cookie_details_page', array(
    'label' => __('Страница с подробной информацией о cookies', 'newscore'),
    'description' => __('Выберите страницу с детальным описанием использования cookies', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'dropdown-pages',
));

// Возрастные ограничения
$wp_customize->add_setting('show_age_restriction', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('show_age_restriction', array(
    'label' => __('Включить возрастное ограничение 18+', 'newscore'),
    'description' => __('ФЗ-436 "О защите детей от информации"', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('age_restriction_sitewide', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('age_restriction_sitewide', array(
    'label' => __('Возрастное ограничение для всего сайта', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('age_title', array(
    'default' => 'Внимание! Возрастное ограничение 18+',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('age_title', array(
    'label' => __('Заголовок возрастного ограничения', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('age_text', array(
    'default' => 'Содержимое этого сайта предназначено для лиц, достигших 18 лет. Подтвердите свой возраст для продолжения.',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('age_text', array(
    'label' => __('Текст возрастного ограничения', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

$wp_customize->add_setting('age_warning', array(
    'default' => 'Предоставляя недостоверную информацию, вы нарушаете законодательство РФ.',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('age_warning', array(
    'label' => __('Предупреждение о недостоверной информации', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

// Тексты для возрастных маркировок
$age_ratings = array('6+', '12+', '16+', '18+');
foreach ($age_ratings as $rating) {
    $wp_customize->add_setting('age_' . $rating . '_text', array(
        'default' => 'Материал для лиц старше ' . $rating,
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('age_' . $rating . '_text', array(
        'label' => sprintf(__('Текст для маркировки %s', 'newscore'), $rating),
        'section' => 'newscore_roskomnadzor',
        'type' => 'text',
    ));
}

// Персональные данные
$wp_customize->add_setting('personal_data_text', array(
    'default' => 'Нажимая кнопку, я соглашаюсь на обработку моих персональных данных в соответствии с Федеральным законом № 152-ФЗ «О персональных данных» и принимаю условия Пользовательского соглашения',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('personal_data_text', array(
    'label' => __('Текст согласия на обработку персональных данных', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

// Информация о СМИ
$wp_customize->add_setting('show_media_info', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('show_media_info', array(
    'label' => __('Показывать информацию о СМИ', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('media_registration_number', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('media_registration_number', array(
    'label' => __('Номер свидетельства о регистрации СМИ', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('media_registration_date', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('media_registration_date', array(
    'label' => __('Дата регистрации СМИ', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('media_editor', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('media_editor', array(
    'label' => __('Главный редактор', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('media_email', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_email',
));

$wp_customize->add_control('media_email', array(
    'label' => __('Email редакции', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'email',
));

// Юридическая информация
$wp_customize->add_setting('show_legal_info', array(
    'default' => false,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('show_legal_info', array(
    'label' => __('Показывать юридическую информацию', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('legal_name', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('legal_name', array(
    'label' => __('Название организации', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('legal_inn', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('legal_inn', array(
    'label' => __('ИНН', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('legal_ogrn', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('legal_ogrn', array(
    'label' => __('ОГРН', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('legal_address', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('legal_address', array(
    'label' => __('Юридический адрес', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

$wp_customize->add_setting('legal_phone', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('legal_phone', array(
    'label' => __('Телефон', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

// Запрещенный контент
$wp_customize->add_setting('prohibited_content_warning', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('prohibited_content_warning', array(
    'label' => __('Предупреждение о запрещенном контенте', 'newscore'),
    'description' => __('Текст предупреждения о соответствии законодательству РФ (ФЗ-149, ФЗ-114 и др.)', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

// Политика конфиденциальности
$wp_customize->add_setting('privacy_policy_page', array(
    'default' => '',
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control('privacy_policy_page', array(
    'label' => __('Страница политики конфиденциальности', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'dropdown-pages',
));

$wp_customize->add_setting('privacy_policy_date', array(
    'default' => date('d.m.Y'),
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('privacy_policy_date', array(
    'label' => __('Дата вступления в силу политики', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('privacy_last_update', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control('privacy_last_update', array(
    'label' => __('Дата последнего обновления политики', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'text',
));

$wp_customize->add_setting('privacy_contact_email', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_email',
));

$wp_customize->add_control('privacy_contact_email', array(
    'label' => __('Email для вопросов о персональных данных', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'email',
));

// GDPR/ФЗ-152 настройки
$wp_customize->add_setting('enable_gdpr_features', array(
    'default' => true,
    'sanitize_callback' => 'wp_validate_boolean',
));

$wp_customize->add_control('enable_gdpr_features', array(
    'label' => __('Включить функции GDPR/ФЗ-152', 'newscore'),
    'description' => __('Экспорт и удаление персональных данных', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'checkbox',
));

$wp_customize->add_setting('data_retention_period', array(
    'default' => '365',
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control('data_retention_period', array(
    'label' => __('Срок хранения персональных данных (дней)', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'number',
    'input_attrs' => array(
        'min' => 1,
        'max' => 3650,
        'step' => 1,
    ),
));

// Дополнительные юридические тексты
$wp_customize->add_setting('disclaimer_text', array(
    'default' => 'Администрация сайта не несет ответственности за содержание комментариев пользователей. Все спорные вопросы решаются в соответствии с законодательством РФ.',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('disclaimer_text', array(
    'label' => __('Текст ограничения ответственности', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));

$wp_customize->add_setting('copyright_notice', array(
    'default' => 'Все материалы сайта защищены законом об авторском праве. При использовании материалов активная ссылка на источник обязательна.',
    'sanitize_callback' => 'sanitize_textarea_field',
));

$wp_customize->add_control('copyright_notice', array(
    'label' => __('Уведомление об авторских правах', 'newscore'),
    'section' => 'newscore_roskomnadzor',
    'type' => 'textarea',
));
}
add_action('customize_register', 'newscore_customizer_settings');