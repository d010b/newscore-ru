<?php
/**
 * Roskomnadzor widgets
 */

// Виджет юридической информации
class Newscore_Legal_Info_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'newscore_legal_info',
            __('Юридическая информация', 'newscore'),
            array('description' => __('Виджет с юридической информацией для футера', 'newscore'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        echo '<div class="legal-widget-content">';
        
        if (!empty($instance['legal_text'])) {
            echo '<div class="legal-text">' . wp_kses_post($instance['legal_text']) . '</div>';
        }
        
        if (!empty($instance['show_privacy_link']) && get_theme_mod('privacy_policy_page')) {
            echo '<a href="' . esc_url(get_permalink(get_theme_mod('privacy_policy_page'))) . '" class="legal-link">';
            echo esc_html__('Политика конфиденциальности', 'newscore');
            echo '</a>';
        }
        
        if (!empty($instance['show_agreement_link'])) {
            $agreement_page = get_page_by_title('Пользовательское соглашение');
            if ($agreement_page) {
                echo '<a href="' . esc_url(get_permalink($agreement_page->ID)) . '" class="legal-link">';
                echo esc_html__('Пользовательское соглашение', 'newscore');
                echo '</a>';
            }
        }
        
        if (!empty($instance['show_cookie_link']) && get_theme_mod('cookie_details_page')) {
            echo '<a href="' . esc_url(get_permalink(get_theme_mod('cookie_details_page'))) . '" class="legal-link">';
            echo esc_html__('Использование cookies', 'newscore');
            echo '</a>';
        }
        
        echo '</div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Юридическая информация', 'newscore');
        $legal_text = !empty($instance['legal_text']) ? $instance['legal_text'] : '';
        $show_privacy_link = !empty($instance['show_privacy_link']) ? $instance['show_privacy_link'] : false;
        $show_agreement_link = !empty($instance['show_agreement_link']) ? $instance['show_agreement_link'] : false;
        $show_cookie_link = !empty($instance['show_cookie_link']) ? $instance['show_cookie_link'] : false;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('legal_text'); ?>"><?php _e('Текст:', 'newscore'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('legal_text'); ?>" 
                      name="<?php echo $this->get_field_name('legal_text'); ?>" rows="5"><?php 
                echo esc_textarea($legal_text); 
            ?></textarea>
        </p>
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo $this->get_field_id('show_privacy_link'); ?>" 
                   name="<?php echo $this->get_field_name('show_privacy_link'); ?>" 
                   <?php checked($show_privacy_link); ?>>
            <label for="<?php echo $this->get_field_id('show_privacy_link'); ?>">
                <?php _e('Показывать ссылку на политику конфиденциальности', 'newscore'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo $this->get_field_id('show_agreement_link'); ?>" 
                   name="<?php echo $this->get_field_name('show_agreement_link'); ?>" 
                   <?php checked($show_agreement_link); ?>>
            <label for="<?php echo $this->get_field_id('show_agreement_link'); ?>">
                <?php _e('Показывать ссылку на пользовательское соглашение', 'newscore'); ?>
            </label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" 
                   id="<?php echo $this->get_field_id('show_cookie_link'); ?>" 
                   name="<?php echo $this->get_field_name('show_cookie_link'); ?>" 
                   <?php checked($show_cookie_link); ?>>
            <label for="<?php echo $this->get_field_id('show_cookie_link'); ?>">
                <?php _e('Показывать ссылку на использование cookies', 'newscore'); ?>
            </label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['legal_text'] = wp_kses_post($new_instance['legal_text']);
        $instance['show_privacy_link'] = !empty($new_instance['show_privacy_link']);
        $instance['show_agreement_link'] = !empty($new_instance['show_agreement_link']);
        $instance['show_cookie_link'] = !empty($new_instance['show_cookie_link']);
        return $instance;
    }
}

// Виджет контактов редакции
class Newscore_Editorial_Contacts_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'newscore_editorial_contacts',
            __('Контакты редакции', 'newscore'),
            array('description' => __('Контактная информация редакции для СМИ', 'newscore'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        echo '<div class="editorial-contacts">';
        
        if (get_theme_mod('media_editor')) {
            echo '<div class="contact-item">';
            echo '<strong>' . __('Главный редактор:', 'newscore') . '</strong> ';
            echo '<span>' . esc_html(get_theme_mod('media_editor')) . '</span>';
            echo '</div>';
        }
        
        if (get_theme_mod('media_email')) {
            echo '<div class="contact-item">';
            echo '<strong>' . __('Email редакции:', 'newscore') . '</strong> ';
            echo '<a href="mailto:' . esc_attr(get_theme_mod('media_email')) . '">';
            echo esc_html(get_theme_mod('media_email'));
            echo '</a>';
            echo '</div>';
        }
        
        if (get_theme_mod('media_phone')) {
            echo '<div class="contact-item">';
            echo '<strong>' . __('Телефон редакции:', 'newscore') . '</strong> ';
            echo '<a href="tel:' . esc_attr(preg_replace('/[^0-9+]/', '', get_theme_mod('media_phone'))) . '">';
            echo esc_html(get_theme_mod('media_phone'));
            echo '</a>';
            echo '</div>';
        }
        
        if (!empty($instance['additional_info'])) {
            echo '<div class="additional-info">';
            echo wp_kses_post($instance['additional_info']);
            echo '</div>';
        }
        
        echo '</div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Контакты редакции', 'newscore');
        $additional_info = !empty($instance['additional_info']) ? $instance['additional_info'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('additional_info'); ?>"><?php _e('Дополнительная информация:', 'newscore'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('additional_info'); ?>" 
                      name="<?php echo $this->get_field_name('additional_info'); ?>" rows="4"><?php 
                echo esc_textarea($additional_info); 
            ?></textarea>
            <small>Например: время работы, дни приема материалов</small>
        </p>
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
    register_widget('Newscore_Legal_Info_Widget');
    register_widget('Newscore_Editorial_Contacts_Widget');
}
add_action('widgets_init', 'newscore_register_roskom_widgets');