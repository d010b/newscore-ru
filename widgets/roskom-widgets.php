<?php
/**
 * Roskomnadzor widgets - Improved version
 */

// Виджет юридической информации с улучшенной безопасностью
class Newscore_Legal_Info_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'newscore_legal_info',
            esc_html__('Юридическая информация', 'newscore'),
            array(
                'description' => esc_html__('Виджет с юридической информацией для футера', 'newscore'),
                'customize_selective_refresh' => true
            )
        );
        
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'widgets.php') {
            wp_enqueue_style(
                'newscore-widgets-admin',
                get_template_directory_uri() . '/assets/css/widgets-admin.css',
                array(),
                '1.0.0'
            );
        }
    }
    
    public function widget($args, $instance) {
        // Безопасный вывод данных
        echo wp_kses_post($args['before_widget']);
        
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        
        if ($title) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        
        echo '<div class="legal-widget-content" role="complementary">';
        
        // Текст виджета
        if (!empty($instance['legal_text'])) {
            echo '<div class="legal-text">' . wp_kses_post(wpautop($instance['legal_text'])) . '</div>';
        }
        
        echo '<div class="legal-links">';
        
        // Ссылка на политику конфиденциальности
        if (!empty($instance['show_privacy_link'])) {
            $privacy_page_id = get_theme_mod('privacy_policy_page');
            if ($privacy_page_id && get_post_status($privacy_page_id) === 'publish') {
                $privacy_url = esc_url(get_permalink($privacy_page_id));
                echo '<a href="' . $privacy_url . '" class="legal-link" rel="privacy-policy">';
                echo '<span class="link-icon">🔒</span>';
                echo esc_html__('Политика конфиденциальности', 'newscore');
                echo '</a>';
            } elseif (current_user_can('edit_theme_options')) {
                echo '<span class="legal-link-missing">';
                echo esc_html__('Страница политики конфиденциальности не настроена', 'newscore');
                echo '</span>';
            }
        }
        
        // Ссылка на пользовательское соглашение
        if (!empty($instance['show_agreement_link'])) {
            $agreement_page = get_page_by_path('user-agreement') ?: get_page_by_title('Пользовательское соглашение');
            
            if ($agreement_page && get_post_status($agreement_page->ID) === 'publish') {
                $agreement_url = esc_url(get_permalink($agreement_page->ID));
                echo '<a href="' . $agreement_url . '" class="legal-link" rel="terms-of-service">';
                echo '<span class="link-icon">📝</span>';
                echo esc_html__('Пользовательское соглашение', 'newscore');
                echo '</a>';
            } elseif (current_user_can('edit_theme_options')) {
                echo '<span class="legal-link-missing">';
                echo esc_html__('Пользовательское соглашение не настроено', 'newscore');
                echo '</span>';
            }
        }
        
        // Ссылка на использование cookies
        if (!empty($instance['show_cookie_link'])) {
            $cookie_page_id = get_theme_mod('cookie_details_page');
            if ($cookie_page_id && get_post_status($cookie_page_id) === 'publish') {
                $cookie_url = esc_url(get_permalink($cookie_page_id));
                echo '<a href="' . $cookie_url . '" class="legal-link" rel="cookie-policy">';
                echo '<span class="link-icon">🍪</span>';
                echo esc_html__('Использование cookies', 'newscore');
                echo '</a>';
            } elseif (current_user_can('edit_theme_options')) {
                echo '<span class="legal-link-missing">';
                echo esc_html__('Страница cookies не настроена', 'newscore');
                echo '</span>';
            }
        }
        
        // Ссылка на импрессум (если настроена)
        if (!empty($instance['show_impressum_link'])) {
            $impressum_page = get_page_by_path('impressum') ?: get_page_by_title('Импрессум');
            
            if ($impressum_page && get_post_status($impressum_page->ID) === 'publish') {
                $impressum_url = esc_url(get_permalink($impressum_page->ID));
                echo '<a href="' . $impressum_url . '" class="legal-link" rel="impressum">';
                echo '<span class="link-icon">🏢</span>';
                echo esc_html__('Импрессум', 'newscore');
                echo '</a>';
            }
        }
        
        echo '</div>'; // .legal-links
        
        // Информация о правах
        if (!empty($instance['show_copyright'])) {
            echo '<div class="copyright-info">';
            echo '© ' . date('Y') . ' ' . esc_html(get_bloginfo('name')) . '. ';
            echo esc_html__('Все права защищены.', 'newscore');
            echo '</div>';
        }
        
        echo '</div>'; // .legal-widget-content
        echo wp_kses_post($args['after_widget']);
    }
    
    public function form($instance) {
        // Значения по умолчанию
        $defaults = array(
            'title'                 => esc_html__('Юридическая информация', 'newscore'),
            'legal_text'            => '',
            'show_privacy_link'     => true,
            'show_agreement_link'   => true,
            'show_cookie_link'      => true,
            'show_impressum_link'   => false,
            'show_copyright'        => true
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        // Безопасное экранирование
        $title = esc_attr($instance['title']);
        $legal_text = esc_textarea($instance['legal_text']);
        ?>
        
        <div class="newscore-widget-form">
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Заголовок:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                       type="text" 
                       value="<?php echo esc_html(); ?>"
                       placeholder="<?php esc_attr_e('Юридическая информация', 'newscore'); ?>">
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('legal_text')); ?>">
                    <?php esc_html_e('Текст:', 'newscore'); ?>
                </label>
                <textarea class="widefat" 
                          id="<?php echo esc_attr($this->get_field_id('legal_text')); ?>" 
                          name="<?php echo esc_attr($this->get_field_name('legal_text')); ?>" 
                          rows="5"
                          placeholder="<?php esc_attr_e('Дополнительная юридическая информация...', 'newscore'); ?>"><?php echo esc_html(); ?></textarea>
                <small class="description">
                    <?php esc_html_e('HTML теги разрешены: a, strong, em, br, p', 'newscore'); ?>
                </small>
            </p>
            
            <div class="widget-checkbox-group">
                <h4><?php esc_html_e('Отображать ссылки:', 'newscore'); ?></h4>
                
                <p>
                    <input class="checkbox" 
                           type="checkbox" 
                           id="<?php echo esc_attr($this->get_field_id('show_privacy_link')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_privacy_link')); ?>" 
                           <?php checked($instance['show_privacy_link']); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_privacy_link')); ?>">
                        <?php esc_html_e('Политика конфиденциальности', 'newscore'); ?>
                    </label>
                </p>
                
                <p>
                    <input class="checkbox" 
                           type="checkbox" 
                           id="<?php echo esc_attr($this->get_field_id('show_agreement_link')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_agreement_link')); ?>" 
                           <?php checked($instance['show_agreement_link']); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_agreement_link')); ?>">
                        <?php esc_html_e('Пользовательское соглашение', 'newscore'); ?>
                    </label>
                </p>
                
                <p>
                    <input class="checkbox" 
                           type="checkbox" 
                           id="<?php echo esc_attr($this->get_field_id('show_cookie_link')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_cookie_link')); ?>" 
                           <?php checked($instance['show_cookie_link']); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_cookie_link')); ?>">
                        <?php esc_html_e('Использование cookies', 'newscore'); ?>
                    </label>
                </p>
                
                <p>
                    <input class="checkbox" 
                           type="checkbox" 
                           id="<?php echo esc_attr($this->get_field_id('show_impressum_link')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_impressum_link')); ?>" 
                           <?php checked($instance['show_impressum_link']); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_impressum_link')); ?>">
                        <?php esc_html_e('Импрессум (для юридических лиц)', 'newscore'); ?>
                    </label>
                </p>
                
                <p>
                    <input class="checkbox" 
                           type="checkbox" 
                           id="<?php echo esc_attr($this->get_field_id('show_copyright')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_copyright')); ?>" 
                           <?php checked($instance['show_copyright']); ?>>
                    <label for="<?php echo esc_attr($this->get_field_id('show_copyright')); ?>">
                        <?php esc_html_e('Информация об авторских правах', 'newscore'); ?>
                    </label>
                </p>
            </div>
            
            <?php if (current_user_can('edit_theme_options')) : ?>
                <div class="widget-admin-notice">
                    <small>
                        <?php 
                        printf(
                            esc_html__('Настройте страницы в %sНастройки темы → Юридическая информация%s', 'newscore'),
                            '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=newscore_legal')) . '">',
                            '</a>'
                        ); 
                        ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        // Безопасное обновление полей
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['legal_text'] = wp_kses_post($new_instance['legal_text']);
        
        // Логические поля
        $instance['show_privacy_link'] = !empty($new_instance['show_privacy_link']);
        $instance['show_agreement_link'] = !empty($new_instance['show_agreement_link']);
        $instance['show_cookie_link'] = !empty($new_instance['show_cookie_link']);
        $instance['show_impressum_link'] = !empty($new_instance['show_impressum_link']);
        $instance['show_copyright'] = !empty($new_instance['show_copyright']);
        
        return $instance;
    }
}

// Виджет контактов редакции с улучшенной безопасностью
class Newscore_Editorial_Contacts_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'newscore_editorial_contacts',
            esc_html__('Контакты редакции', 'newscore'),
            array(
                'description' => esc_html__('Контактная информация редакции для СМИ', 'newscore'),
                'customize_selective_refresh' => true
            )
        );
    }
    
    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);
        
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        
        if ($title) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        
        echo '<div class="editorial-contacts" role="contentinfo">';
        
        // Главный редактор
        $media_editor = get_theme_mod('media_editor');
        if ($media_editor) {
            echo '<div class="contact-item" itemprop="employee" itemscope itemtype="https://schema.org/Person">';
            echo '<strong>' . esc_html__('Главный редактор:', 'newscore') . '</strong> ';
            echo '<span itemprop="name">' . esc_html($media_editor) . '</span>';
            
            // Дополнительная информация о редакторе
            $editor_email = get_theme_mod('media_editor_email');
            if ($editor_email && is_email($editor_email)) {
                echo '<br><small itemprop="email">';
                echo '<a href="mailto:' . esc_attr($editor_email) . '">';
                echo esc_html($editor_email);
                echo '</a>';
                echo '</small>';
            }
            echo '</div>';
        }
        
        // Email редакции
        $media_email = get_theme_mod('media_email');
        if ($media_email && is_email($media_email)) {
            echo '<div class="contact-item" itemprop="email">';
            echo '<strong>' . esc_html__('Email редакции:', 'newscore') . '</strong> ';
            echo '<a href="mailto:' . esc_attr($media_email) . '" itemprop="email">';
            echo esc_html($media_email);
            echo '</a>';
            echo '</div>';
        }
        
        // Телефон редакции
        $media_phone = get_theme_mod('media_phone');
        if ($media_phone) {
            $clean_phone = preg_replace('/[^0-9+]/', '', $media_phone);
            echo '<div class="contact-item" itemprop="telephone">';
            echo '<strong>' . esc_html__('Телефон редакции:', 'newscore') . '</strong> ';
            echo '<a href="tel:' . esc_attr($clean_phone) . '" itemprop="telephone">';
            echo esc_html($media_phone);
            echo '</a>';
            echo '</div>';
        }
        
        // Адрес редакции
        $media_address = get_theme_mod('media_address');
        if ($media_address) {
            echo '<div class="contact-item" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
            echo '<strong>' . esc_html__('Адрес:', 'newscore') . '</strong> ';
            echo '<span itemprop="streetAddress">' . esc_html($media_address) . '</span>';
            
            // Город и индекс если доступны
            $media_city = get_theme_mod('media_city');
            $media_zip = get_theme_mod('media_zip');
            
            if ($media_city) {
                echo '<br><span itemprop="addressLocality">' . esc_html($media_city) . '</span>';
            }
            
            if ($media_zip) {
                echo ', <span itemprop="postalCode">' . esc_html($media_zip) . '</span>';
            }
            echo '</div>';
        }
        
        // Время работы
        $working_hours = get_theme_mod('media_working_hours');
        if ($working_hours) {
            echo '<div class="contact-item">';
            echo '<strong>' . esc_html__('Время работы:', 'newscore') . '</strong> ';
            echo '<span>' . esc_html($working_hours) . '</span>';
            echo '</div>';
        }
        
        // Дополнительная информация из виджета
        if (!empty($instance['additional_info'])) {
            echo '<div class="additional-info">';
            echo wp_kses_post(wpautop($instance['additional_info']));
            echo '</div>';
        }
        
        // Кнопки для связи
        echo '<div class="contact-actions">';
        
        $contact_form_page = get_theme_mod('media_contact_form_page');
        if ($contact_form_page && get_post_status($contact_form_page) === 'publish') {
            echo '<a href="' . esc_url(get_permalink($contact_form_page)) . '" class="contact-button">';
            echo esc_html__('Написать редакции', 'newscore');
            echo '</a>';
        }
        
        $media_submission_page = get_theme_mod('media_submission_page');
        if ($media_submission_page && get_post_status($media_submission_page) === 'publish') {
            echo '<a href="' . esc_url(get_permalink($media_submission_page)) . '" class="contact-button secondary">';
            echo esc_html__('Отправить материал', 'newscore');
            echo '</a>';
        }
        
        echo '</div>';
        
        echo '</div>'; // .editorial-contacts
        echo wp_kses_post($args['after_widget']);
    }
    
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Контакты редакции', 'newscore'),
            'additional_info' => ''
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = esc_attr($instance['title']);
        $additional_info = esc_textarea($instance['additional_info']);
        ?>
        
        <div class="newscore-widget-form">
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Заголовок:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                       type="text" 
                       value="<?php echo esc_html(); ?>">
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('additional_info')); ?>">
                    <?php esc_html_e('Дополнительная информация:', 'newscore'); ?>
                </label>
                <textarea class="widefat" 
                          id="<?php echo esc_attr($this->get_field_id('additional_info')); ?>" 
                          name="<?php echo esc_attr($this->get_field_name('additional_info')); ?>" 
                          rows="4"><?php echo esc_html(); ?></textarea>
                <small class="description">
                    <?php esc_html_e('Например: время работы, дни приема материалов, условия сотрудничества', 'newscore'); ?>
                </small>
            </p>
            
            <?php if (current_user_can('edit_theme_options')) : ?>
                <div class="widget-admin-notice">
                    <small>
                        <?php 
                        printf(
                            esc_html__('Настройте контакты редакции в %sНастройки темы → Контакты редакции%s', 'newscore'),
                            '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=newscore_media')) . '">',
                            '</a>'
                        ); 
                        ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['additional_info'] = wp_kses_post($new_instance['additional_info']);
        
        return $instance;
    }
}

// Регистрация виджетов
function newscore_register_roskom_widgets() {
    // Проверяем, не зарегистрированы ли виджеты уже
    if (!is_registered_sidebar('footer-legal') && is_active_sidebar('footer-legal')) {
        return;
    }
    
    register_widget('Newscore_Legal_Info_Widget');
    register_widget('Newscore_Editorial_Contacts_Widget');
}

add_action('widgets_init', 'newscore_register_roskom_widgets');

// Создаем сайдбар для юридической информации
function newscore_register_legal_sidebar() {
    register_sidebar(array(
        'name'          => esc_html__('Юридическая информация (футер)', 'newscore'),
        'id'            => 'footer-legal',
        'description'   => esc_html__('Добавьте виджеты юридической информации в футер', 'newscore'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}

add_action('widgets_init', 'newscore_register_legal_sidebar');
?>