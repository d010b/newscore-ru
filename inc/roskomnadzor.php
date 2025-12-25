<?php
/**
 * Roskomnadzor requirements implementation - Безопасная версия
 */

// Создание таблицы для хранения согласий
function newscore_roskomnadzor_init() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'newscore_consents';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        consent_type varchar(50) NOT NULL,
        user_hash varchar(64) NOT NULL,
        consent_data text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_consent_type (consent_type),
        KEY idx_created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'newscore_roskomnadzor_init');

/**
 * Безопасная запись согласия
 */
function newscore_record_consent_safe($type, $data = array()) {
    global $wpdb;
    
    if (empty($type) || !is_array($data)) {
        return false;
    }
    
    $table_name = $wpdb->prefix . 'newscore_consents';
    
    // Генерируем безопасный хеш пользователя
    $user_hash = wp_hash(wp_get_session_token() . NONCE_SALT);
    
    $consent_data = array(
        'consent_type' => sanitize_key($type),
        'user_hash' => $user_hash,
        'consent_data' => wp_json_encode(wp_kses_post_deep($data)),
    );
    
    $format = array('%s', '%s', '%s');
    
    $result = $wpdb->insert($table_name, $consent_data, $format);
    
    return $result !== false ? $wpdb->insert_id : false;
}

/**
 * Форма согласия на обработку персональных данных
 */
function newscore_personal_data_form_secure($fields) {
    $consent_text = get_theme_mod('personal_data_text', 
        'Нажимая кнопку, я соглашаюсь на обработку моих персональных данных в соответствии с Федеральным законом № 152-ФЗ «О персональных данных»'
    );
    
    $fields['comment_field'] .= '
    <div class="personal-data-consent">
        <label class="consent-checkbox">
            <input type="checkbox" name="personal_data_consent" required>
            <span class="consent-text">' . esc_html($consent_text) . '</span>
        </label>
    </div>';
    
    return $fields;
}
add_filter('comment_form_defaults', 'newscore_personal_data_form_secure');

/**
 * Проверка согласия при отправке комментария
 */
function newscore_verify_comment_consent($commentdata) {
    if (!isset($_POST['personal_data_consent'])) {
        wp_die('<strong>Ошибка:</strong> Вы должны согласиться на обработку персональных данных.');
    }
    return $commentdata;
}
add_filter('preprocess_comment', 'newscore_verify_comment_consent');

/**
 * Информация о СМИ в футере
 */
function newscore_media_info_safe() {
    if (get_theme_mod('show_media_info', false)) {
        echo '<div class="media-info">';
        
        if (get_theme_mod('media_registration_number')) {
            echo '<div class="media-registration">
                <strong>Свидетельство о регистрации СМИ:</strong> ' . 
                esc_html(get_theme_mod('media_registration_number')) . 
                ' от ' . esc_html(get_theme_mod('media_registration_date', date('d.m.Y'))) . 
            '</div>';
        }
        
        if (get_theme_mod('media_editor')) {
            echo '<div class="media-editor">
                <strong>Главный редактор:</strong> ' . 
                esc_html(get_theme_mod('media_editor')) . 
            '</div>';
        }
        
        if (get_theme_mod('media_email')) {
            $email = sanitize_email(get_theme_mod('media_email'));
            echo '<div class="media-email">
                <strong>Электронная почта редакции:</strong> ' . 
                '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>
            </div>';
        }
        
        echo '</div>';
    }
}
add_action('newscore_footer_before_copyright', 'newscore_media_info_safe');

/**
 * Шорткод для политики конфиденциальности
 */
function newscore_privacy_policy_shortcode_secure($atts) {
    $atts = shortcode_atts(array(
        'show_date' => true,
    ), $atts);
    
    ob_start();
    ?>
    <div class="privacy-policy-shortcode">
        <h3>Политика конфиденциальности</h3>
        
        <?php if ($atts['show_date'] && get_theme_mod('privacy_policy_date')) : ?>
            <p class="policy-date">
                Дата вступления в силу: <?php echo esc_html(get_theme_mod('privacy_policy_date')); ?>
            </p>
        <?php endif; ?>
        
        <div class="policy-sections">
            <section>
                <h4>1. Сбор информации</h4>
                <p>Мы собираем информацию, которую вы предоставляете при регистрации, подписке или заполнении форм на сайте.</p>
            </section>
            
            <section>
                <h4>2. Использование информации</h4>
                <p>Собранная информация используется для улучшения работы сайта, персонализации контента и отправки уведомлений.</p>
            </section>
            
            <section>
                <h4>3. Защита информации</h4>
                <p>Мы принимаем меры для защиты ваших персональных данных от несанкционированного доступа.</p>
            </section>
            
            <section>
                <h4>4. Контакты</h4>
                <p>По вопросам обработки персональных данных обращайтесь по email: 
                <?php echo esc_html(get_theme_mod('privacy_contact_email', get_bloginfo('admin_email'))); ?></p>
            </section>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('privacy_policy', 'newscore_privacy_policy_shortcode_secure');

/**
 * GDPR/ФЗ-152 экспорт данных пользователя
 */
function newscore_gdpr_data_export_secure() {
    if (!current_user_can('read')) {
        wp_die('У вас нет прав для выполнения этого действия.');
    }
    
    $user_id = get_current_user_id();
    $user_data = get_userdata($user_id);
    
    if (!$user_data) {
        wp_die('Пользователь не найден.');
    }
    
    $export_data = array(
        'user_id' => $user_id,
        'username' => $user_data->user_login,
        'email' => $user_data->user_email,
        'display_name' => $user_data->display_name,
        'registration_date' => $user_data->user_registered,
    );
    
    // JSON файл
    $json_data = wp_json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="user-data-export-' . $user_id . '-' . date('Y-m-d') . '.json"');
    
    echo $json_data;
    exit;
}

/**
 * Добавляем пункты в админ-панель для GDPR
 */
function newscore_gdpr_admin_menu_safe() {
    add_users_page(
        'Экспорт данных',
        'GDPR Экспорт',
        'read',
        'gdpr-export',
        'newscore_gdpr_export_page_safe'
    );
}
add_action('admin_menu', 'newscore_gdpr_admin_menu_safe');

function newscore_gdpr_export_page_safe() {
    if (!current_user_can('read')) {
        wp_die('У вас нет прав для доступа к этой странице.');
    }
    ?>
    <div class="wrap">
        <h1>Экспорт данных</h1>
        <div class="gdpr-actions">
            <h2>Экспорт своих данных</h2>
            <p>Вы можете скачать все ваши персональные данные, хранящиеся на сайте.</p>
            <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=export_personal_data'), 'gdpr_export'); ?>" class="button button-primary">
                Экспортировать мои данные
            </a>
        </div>
    </div>
    <?php
}

// AJAX обработчик для экспорта данных
function newscore_gdpr_ajax_export() {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'gdpr_export')) {
        wp_die('Недействительный токен безопасности.');
    }
    
    if (!is_user_logged_in()) {
        wp_die('Вы должны быть авторизованы.');
    }
    
    newscore_gdpr_data_export_secure();
}
add_action('wp_ajax_export_personal_data', 'newscore_gdpr_ajax_export');