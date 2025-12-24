<?php
/**
 * Roskomnadzor requirements implementation
 */

// Основные настройки Роскомнадзора
function newscore_roskomnadzor_init() {
    // Создаем таблицу для хранения согласий (если нужно)
    global $wpdb;
    $table_name = $wpdb->prefix . 'newscore_consents';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_ip varchar(45) NOT NULL,
        user_agent text NOT NULL,
        consent_type varchar(50) NOT NULL,
        consent_data text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'newscore_roskomnadzor_init');

// Cookie уведомление
function newscore_cookie_notice() {
    if (get_theme_mod('show_cookie_notice', true) && !isset($_COOKIE['newscore_cookie_accepted'])) {
        ?>
        <div id="roskom-cookie-notice" class="roskom-cookie-notice">
            <div class="cookie-container">
                <div class="cookie-content">
                    <h4><?php echo esc_html(get_theme_mod('cookie_title', 'Использование файлов cookie')); ?></h4>
                    <p><?php echo esc_html(get_theme_mod('cookie_text', 'Этот сайт использует файлы cookie для улучшения работы и аналитики. Продолжая использовать сайт, вы соглашаетесь с Политикой конфиденциальности и использованием файлов cookie.')); ?></p>
                    <div class="cookie-links">
                        <a href="<?php echo esc_url(get_permalink(get_theme_mod('privacy_policy_page'))); ?>" class="cookie-link">
                            <?php esc_html_e('Политика конфиденциальности', 'newscore'); ?>
                        </a>
                        <?php if (get_theme_mod('cookie_details_page')) : ?>
                        <a href="<?php echo esc_url(get_permalink(get_theme_mod('cookie_details_page'))); ?>" class="cookie-link">
                            <?php esc_html_e('Подробнее о cookies', 'newscore'); ?>
                        </a>
                        <?php endif; ?>
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

// Возрастное ограничение 18+
function newscore_age_restriction() {
    if (get_theme_mod('show_age_restriction', false) && !isset($_COOKIE['newscore_age_confirmed'])) {
        // Для всего сайта
        if (get_theme_mod('age_restriction_sitewide', false)) {
            ?>
            <div id="roskom-age-gate" class="roskom-age-gate">
                <div class="age-gate-content">
                    <div class="age-logo">
                        <span class="age-18">18+</span>
                    </div>
                    <h2><?php echo esc_html(get_theme_mod('age_title', 'Внимание! Возрастное ограничение 18+')); ?></h2>
                    <p><?php echo esc_html(get_theme_mod('age_text', 'Содержимое этого сайта предназначено для лиц, достигших 18 лет. Подтвердите свой возраст для продолжения.')); ?></p>
                    
                    <div class="age-form">
                        <div class="age-input-group">
                            <label for="age-day">День</label>
                            <select id="age-day" class="age-select">
                                <option value="">День</option>
                                <?php for ($i = 1; $i <= 31; $i++) : ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="age-input-group">
                            <label for="age-month">Месяц</label>
                            <select id="age-month" class="age-select">
                                <option value="">Месяц</option>
                                <?php
                                $months = array(
                                    '01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель',
                                    '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август',
                                    '09' => 'Сентябрь', '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь'
                                );
                                foreach ($months as $num => $name) {
                                    echo '<option value="' . $num . '">' . $name . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="age-input-group">
                            <label for="age-year">Год</label>
                            <select id="age-year" class="age-select">
                                <option value="">Год</option>
                                <?php
                                $current_year = date('Y');
                                for ($i = $current_year; $i >= $current_year - 100; $i--) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="age-actions">
                        <button type="button" class="age-btn age-exit">
                            <?php esc_html_e('Выйти с сайта', 'newscore'); ?>
                        </button>
                        <button type="button" class="age-btn age-confirm">
                            <?php esc_html_e('Мне есть 18 лет', 'newscore'); ?>
                        </button>
                    </div>
                    
                    <p class="age-warning">
                        <?php echo esc_html(get_theme_mod('age_warning', 'Предоставляя недостоверную информацию, вы нарушаете законодательство РФ.')); ?>
                    </p>
                </div>
            </div>
            <?php
        }
    }
}
add_action('wp_body_open', 'newscore_age_restriction');

// Возрастная маркировка для отдельных записей
function newscore_post_age_badge($content) {
    if (is_single()) {
        $age_rating = get_post_meta(get_the_ID(), '_age_rating', true);
        if ($age_rating && $age_rating !== '0+') {
            $badge = '<div class="post-age-badge age-' . esc_attr($age_rating) . '">
                <span class="age-label">' . esc_html($age_rating) . '</span>
                <span class="age-text">' . esc_html(get_theme_mod('age_' . $age_rating . '_text', 'Материал для лиц старше ' . $age_rating)) . '</span>
            </div>';
            
            // Добавляем в начало контента
            $content = $badge . $content;
        }
    }
    return $content;
}
add_filter('the_content', 'newscore_post_age_badge', 5);

// Форма согласия на обработку персональных данных
function newscore_personal_data_form($form_html) {
    $consent_text = get_theme_mod('personal_data_text', 'Нажимая кнопку, я соглашаюсь на обработку моих персональных данных в соответствии с Федеральным законом № 152-ФЗ «О персональных данных» и принимаю условия Пользовательского соглашения');
    
    $consent_field = '<div class="personal-data-consent">
        <label class="consent-checkbox">
            <input type="checkbox" name="personal_data_consent" required>
            <span class="consent-text">' . esc_html($consent_text) . '</span>
        </label>
    </div>';
    
    // Вставляем перед кнопкой отправки
    $form_html = str_replace('</form>', $consent_field . '</form>', $form_html);
    
    return $form_html;
}
add_filter('comment_form_defaults', 'newscore_personal_data_form');
add_filter('wpcf7_form_elements', 'newscore_personal_data_form');

// Информация о СМИ в футере
function newscore_media_info() {
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
                <strong>Главный редактор:</strong> ' . esc_html(get_theme_mod('media_editor')) . 
                '</div>';
        }
        
        if (get_theme_mod('media_email')) {
            echo '<div class="media-email">
                <strong>Электронная почта редакции:</strong> ' . 
                '<a href="mailto:' . esc_attr(get_theme_mod('media_email')) . '">' . 
                esc_html(get_theme_mod('media_email')) . '</a></div>';
        }
        
        echo '</div>';
    }
}
add_action('newscore_footer_before_copyright', 'newscore_media_info');

// Предупреждение о запрещенной информации
function newscore_prohibited_content_warning() {
    $warning = get_theme_mod('prohibited_content_warning', '');
    if ($warning) {
        echo '<div class="prohibited-content-warning">
            <p>' . wp_kses_post($warning) . '</p>
        </div>';
    }
}
add_action('newscore_after_post_content', 'newscore_prohibited_content_warning');

// Блок с реквизитами в футере
function newscore_legal_info() {
    if (get_theme_mod('show_legal_info', false)) {
        ?>
        <div class="legal-info">
            <h4>Юридическая информация</h4>
            <div class="legal-details">
                <?php if (get_theme_mod('legal_name')) : ?>
                    <p><strong>Название организации:</strong> <?php echo esc_html(get_theme_mod('legal_name')); ?></p>
                <?php endif; ?>
                
                <?php if (get_theme_mod('legal_inn')) : ?>
                    <p><strong>ИНН:</strong> <?php echo esc_html(get_theme_mod('legal_inn')); ?></p>
                <?php endif; ?>
                
                <?php if (get_theme_mod('legal_ogrn')) : ?>
                    <p><strong>ОГРН:</strong> <?php echo esc_html(get_theme_mod('legal_ogrn')); ?></p>
                <?php endif; ?>
                
                <?php if (get_theme_mod('legal_address')) : ?>
                    <p><strong>Юридический адрес:</strong> <?php echo esc_html(get_theme_mod('legal_address')); ?></p>
                <?php endif; ?>
                
                <?php if (get_theme_mod('legal_phone')) : ?>
                    <p><strong>Телефон:</strong> <?php echo esc_html(get_theme_mod('legal_phone')); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
add_action('newscore_footer_widgets', 'newscore_legal_info', 5);

// Шорткод для политики конфиденциальности
function newscore_privacy_policy_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_date' => true,
        'show_update' => true
    ), $atts);
    
    ob_start();
    ?>
    <div class="privacy-policy-shortcode">
        <h3>Политика конфиденциальности</h3>
        
        <?php if ($atts['show_date'] && get_theme_mod('privacy_policy_date')) : ?>
            <p class="policy-date">Дата вступления в силу: <?php echo esc_html(get_theme_mod('privacy_policy_date')); ?></p>
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
                <h4>4. Cookies</h4>
                <p>Сайт использует файлы cookie для улучшения пользовательского опыта.</p>
            </section>
            
            <section>
                <h4>5. Контакты</h4>
                <p>По вопросам обработки персональных данных обращайтесь по email: <?php echo esc_html(get_theme_mod('privacy_contact_email', get_bloginfo('admin_email'))); ?></p>
            </section>
        </div>
        
        <?php if ($atts['show_update'] && get_theme_mod('privacy_last_update')) : ?>
            <p class="policy-update">Последнее обновление: <?php echo esc_html(get_theme_mod('privacy_last_update')); ?></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('privacy_policy', 'newscore_privacy_policy_shortcode');

// Шорткод для пользовательского соглашения
function newscore_user_agreement_shortcode() {
    ob_start();
    ?>
    <div class="user-agreement-shortcode">
        <h3>Пользовательское соглашение</h3>
        
        <div class="agreement-sections">
            <section>
                <h4>1. Общие положения</h4>
                <p>Используя данный сайт, вы соглашаетесь с условиями настоящего соглашения.</p>
            </section>
            
            <section>
                <h4>2. Права и обязанности пользователя</h4>
                <p>Пользователь обязуется не нарушать законодательство РФ при использовании сайта.</p>
            </section>
            
            <section>
                <h4>3. Ограничение ответственности</h4>
                <p>Администрация сайта не несет ответственности за содержание внешних ссылок.</p>
            </section>
            
            <section>
                <h4>4. Интеллектуальная собственность</h4>
                <p>Все материалы сайта защищены законом об авторском праве.</p>
            </section>
            
            <section>
                <h4>5. Изменение условий</h4>
                <p>Администрация оставляет за собой право изменять условия соглашения.</p>
            </section>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('user_agreement', 'newscore_user_agreement_shortcode');

// GDPR/ФЗ-152 экспорт данных пользователя
function newscore_gdpr_data_export() {
    if (!current_user_can('read')) {
        wp_die('У вас нет прав для выполнения этого действия.');
    }
    
    $user_id = get_current_user_id();
    $user_data = get_userdata($user_id);
    
    $export_data = array(
        'user_id' => $user_id,
        'username' => $user_data->user_login,
        'email' => $user_data->user_email,
        'display_name' => $user_data->display_name,
        'registration_date' => $user_data->user_registered,
        'comments' => array(),
        'consents' => array()
    );
    
    // Комментарии пользователя
    $comments = get_comments(array(
        'user_id' => $user_id,
        'number' => 100
    ));
    
    foreach ($comments as $comment) {
        $export_data['comments'][] = array(
            'id' => $comment->comment_ID,
            'content' => $comment->comment_content,
            'post_id' => $comment->comment_post_ID,
            'date' => $comment->comment_date
        );
    }
    
    // Создаем JSON файл
    $json_data = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="user-data-export-' . $user_id . '-' . date('Y-m-d') . '.json"');
    
    echo $json_data;
    exit;
}

// Удаление данных пользователя
function newscore_gdpr_data_erase() {
    if (!current_user_can('delete_users')) {
        wp_die('У вас нет прав для выполнения этого действия.');
    }
    
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id) {
        // Анонимизируем пользователя
        $anon_data = array(
            'ID' => $user_id,
            'user_login' => 'deleted_user_' . $user_id,
            'user_email' => 'deleted_' . $user_id . '@example.com',
            'display_name' => 'Удаленный пользователь',
            'first_name' => '',
            'last_name' => ''
        );
        
        wp_update_user($anon_data);
        
        // Анонимизируем комментарии
        $comments = get_comments(array('user_id' => $user_id));
        foreach ($comments as $comment) {
            wp_update_comment(array(
                'comment_ID' => $comment->comment_ID,
                'comment_author' => 'Удаленный пользователь',
                'comment_author_email' => '',
                'comment_author_url' => ''
            ));
        }
        
        echo 'Данные пользователя анонимизированы.';
    }
    
    exit;
}

// Добавляем пункты в админ-панель
function newscore_gdpr_admin_menu() {
    add_users_page(
        'Экспорт данных GDPR',
        'GDPR Экспорт',
        'manage_options',
        'gdpr-export',
        'newscore_gdpr_export_page'
    );
}
add_action('admin_menu', 'newscore_gdpr_admin_menu');

function newscore_gdpr_export_page() {
    ?>
    <div class="wrap">
        <h1>Экспорт данных GDPR/ФЗ-152</h1>
        
        <div class="gdpr-actions">
            <h2>Экспорт своих данных</h2>
            <p>Вы можете скачать все ваши персональные данные, хранящиеся на сайте.</p>
            <a href="<?php echo admin_url('admin-ajax.php?action=export_personal_data'); ?>" class="button button-primary">
                Экспортировать мои данные
            </a>
            
            <?php if (current_user_can('delete_users')) : ?>
            <hr>
            <h2>Управление данными пользователей</h2>
            <form method="get" action="<?php echo admin_url('admin-ajax.php'); ?>">
                <input type="hidden" name="action" value="erase_personal_data">
                <p>
                    <label for="user_id">ID пользователя для анонимизации:</label>
                    <input type="number" id="user_id" name="user_id" required>
                </p>
                <button type="submit" class="button button-secondary">Анонимизировать данные</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// AJAX обработчики
function newscore_gdpr_ajax_handlers() {
    add_action('wp_ajax_export_personal_data', 'newscore_gdpr_data_export');
    add_action('wp_ajax_erase_personal_data', 'newscore_gdpr_data_erase');
}
add_action('init', 'newscore_gdpr_ajax_handlers');

// Метабокс для возрастной маркировки в админке
function newscore_age_rating_meta_box() {
    add_meta_box(
        'age_rating',
        'Возрастная маркировка',
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
        <label for="age_rating">Возрастное ограничение:</label>
        <select name="age_rating" id="age_rating" style="width:100%;">
            <option value="">Без ограничений</option>
            <?php foreach ($ratings as $rating) : ?>
                <option value="<?php echo esc_attr($rating); ?>" <?php selected($current_rating, $rating); ?>>
                    <?php echo esc_html($rating); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p class="description">
        Установите возрастное ограничение согласно ФЗ-436 "О защите детей от информации"
    </p>
    <?php
}

function newscore_save_age_rating($post_id) {
    if (!isset($_POST['age_rating_nonce']) || 
        !wp_verify_nonce($_POST['age_rating_nonce'], 'newscore_age_rating')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['age_rating'])) {
        update_post_meta($post_id, '_age_rating', sanitize_text_field($_POST['age_rating']));
    }
}
add_action('save_post', 'newscore_save_age_rating');