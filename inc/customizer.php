<?php
/**
 * Theme Customizer - Без сервисов Яндекса
 */

if (!function_exists('newscore_customizer_settings')) {
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
        
        // Секция: Роскомнадзор и законодательство РФ
        $wp_customize->add_section('newscore_roskomnadzor', array(
            'title' => __('Роскомнадзор и законодательство', 'newscore'),
            'panel' => 'newscore_settings',
            'priority' => 60,
        ));
        
        // Cookie уведомление
        $wp_customize->add_setting('show_cookie_notice', array(
            'default' => true,
            'sanitize_callback' => 'wp_validate_boolean',
        ));
        
        $wp_customize->add_control('show_cookie_notice', array(
            'label' => __('Показывать уведомление о cookies', 'newscore'),
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
            'default' => 'Этот сайт использует файлы cookie для улучшения работы. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности.',
            'sanitize_callback' => 'sanitize_textarea_field',
        ));
        
        $wp_customize->add_control('cookie_text', array(
            'label' => __('Текст уведомления о cookies', 'newscore'),
            'section' => 'newscore_roskomnadzor',
            'type' => 'textarea',
        ));
        
        $wp_customize->add_setting('privacy_policy_page', array(
            'default' => '',
            'sanitize_callback' => 'absint',
        ));
        
        $wp_customize->add_control('privacy_policy_page', array(
            'label' => __('Страница политики конфиденциальности', 'newscore'),
            'section' => 'newscore_roskomnadzor',
            'type' => 'dropdown-pages',
        ));
        
        // Российские социальные сети
        $wp_customize->add_section('newscore_russian_social', array(
            'title' => __('Российские соцсети', 'newscore'),
            'panel' => 'newscore_settings',
            'priority' => 45,
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
    }
}
add_action('customize_register', 'newscore_customizer_settings');