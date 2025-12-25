<?php
/**
 * NewsCore Shortcode for Test Content Import
 */

// Шорткод для отображения статуса импорта
function newscore_import_status_shortcode($atts) {
    if (!current_user_can('manage_options')) {
        return '';
    }
    
    $atts = shortcode_atts(array(
        'show_link' => 'true'
    ), $atts);
    
    ob_start();
    ?>
    <div class="newscore-import-status">
        <h3>Статус тестового контента</h3>
        
        <?php
        // Проверяем наличие тестового контента
        $test_news = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => 1,
            's' => 'космический телескоп'
        ));
        
        $categories = get_categories(array('hide_empty' => false));
        $widgets = get_option('sidebars_widgets', array());
        $widget_count = 0;
        
        if (isset($widgets['sidebar-1'])) {
            $widget_count = count($widgets['sidebar-1']);
        }
        ?>
        
        <div class="status-items">
            <div class="status-item <?php echo $test_news ? 'status-ok' : 'status-missing'; ?>">
                <span class="status-icon"><?php echo $test_news ? '✓' : '✗'; ?></span>
                <span class="status-text">Тестовая новость</span>
            </div>
            
            <div class="status-item <?php echo count($categories) >= 8 ? 'status-ok' : 'status-missing'; ?>">
                <span class="status-icon"><?php echo count($categories) >= 8 ? '✓' : '✗'; ?></span>
                <span class="status-text">Категории (<?php echo count($categories); ?>/8)</span>
            </div>
            
            <div class="status-item <?php echo $widget_count >= 5 ? 'status-ok' : 'status-missing'; ?>">
                <span class="status-icon"><?php echo $widget_count >= 5 ? '✓' : '✗'; ?></span>
                <span class="status-text">Виджеты (<?php echo $widget_count; ?>/5)</span>
            </div>
            
            <div class="status-item <?php echo get_theme_mod('primary_color') === '#0073aa' ? 'status-ok' : 'status-missing'; ?>">
                <span class="status-icon"><?php echo get_theme_mod('primary_color') === '#0073aa' ? '✓' : '✗'; ?></span>
                <span class="status-text">Настройки темы</span>
            </div>
        </div>
        
        <?php if ($atts['show_link'] === 'true') : ?>
            <div class="import-link">
                <a href="<?php echo admin_url('tools.php?page=newscore-import-test-content'); ?>" class="button button-primary">
                    Импортировать тестовый контент
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .newscore-import-status {
        background: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 20px 0;
    }
    
    .newscore-import-status h3 {
        margin-top: 0;
        color: #0073aa;
        border-bottom: 2px solid #f5f5f5;
        padding-bottom: 10px;
    }
    
    .status-items {
        margin: 15px 0;
    }
    
    .status-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .status-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 10px;
        font-weight: bold;
    }
    
    .status-ok .status-icon {
        background: #d4edda;
        color: #155724;
    }
    
    .status-missing .status-icon {
        background: #f8d7da;
        color: #721c24;
    }
    
    .import-link {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('newscore_import_status', 'newscore_import_status_shortcode');

// Шорткод для быстрого создания тестовой новости
function newscore_create_test_news_shortcode($atts) {
    if (!current_user_can('manage_options')) {
        return '<p>Требуются права администратора.</p>';
    }
    
    if (isset($_POST['newscore_create_test_news'])) {
        check_admin_referer('newscore_test_news_nonce');
        
        $importer = new NewsCore_Test_Content_Importer();
        $result = $importer->create_test_news_post();
        
        if ($result['success']) {
            return '<div class="notice notice-success"><p>' . $result['message'] . '</p>' .
                   '<p><a href="' . $result['edit_link'] . '">Редактировать</a> | ' .
                   '<a href="' . $result['view_link'] . '">Просмотреть</a></p></div>';
        } else {
            return '<div class="notice notice-error"><p>' . $result['message'] . '</p></div>';
        }
    }
    
    ob_start();
    ?>
    <div class="newscore-quick-import">
        <h3>Быстрое создание тестовой новости</h3>
        <form method="post">
            <?php wp_nonce_field('newscore_test_news_nonce'); ?>
            <p>Создать новость о запуске космического телескопа с полным контентом и изображениями.</p>
            <input type="submit" name="newscore_create_test_news" value="Создать тестовую новость" class="button button-primary">
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('newscore_create_test_news', 'newscore_create_test_news_shortcode');