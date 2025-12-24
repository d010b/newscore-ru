<?php
/**
 * Russian specific widgets
 */

// Яндекс.Погода виджет
class Newscore_Yandex_Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'newscore_yandex_weather',
            __('Яндекс.Погода', 'newscore'),
            array('description' => __('Виджет погоды от Яндекс', 'newscore'))
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $city = isset($instance['city']) ? $instance['city'] : 'Москва';
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo '<div class="weather-widget" data-city="' . esc_attr($city) . '">
            <div class="weather-loading">Загрузка погоды...</div>
        </div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : __('Погода', 'newscore');
        $city = isset($instance['city']) ? esc_attr($instance['city']) : 'Москва';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('city'); ?>"><?php _e('Город:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('city'); ?>" 
                   name="<?php echo $this->get_field_name('city'); ?>" type="text" 
                   value="<?php echo $city; ?>">
            <small>Например: Москва, Санкт-Петербург, Новосибирск</small>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['city'] = sanitize_text_field($new_instance['city']);
        return $instance;
    }
}

// Курсы валют виджет
class Newscore_Exchange_Rates_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'newscore_exchange_rates',
            __('Курсы валют', 'newscore'),
            array('description' => __('Курсы валют ЦБ РФ', 'newscore'))
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo '<div class="exchange-rates-widget">
            <div class="exchange-loading">Загрузка курсов...</div>
        </div>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : __('Курсы валют', 'newscore');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo $title; ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        return $instance;
    }
}

// VK Группа виджет
class Newscore_VK_Group_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'newscore_vk_group',
            __('VK Группа', 'newscore'),
            array('description' => __('Виджет группы ВКонтакте', 'newscore'))
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $group_id = isset($instance['group_id']) ? $instance['group_id'] : '';
        
        if (empty($group_id)) return;
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo '<div id="vk_groups"></div>
        <script type="text/javascript">
            VK.Widgets.Group("vk_groups", {
                mode: ' . (isset($instance['mode']) ? intval($instance['mode']) : 0) . ',
                width: "' . (isset($instance['width']) ? esc_js($instance['width']) : 'auto') . '",
                height: "' . (isset($instance['height']) ? esc_js($instance['height']) : '400') . '",
                color1: "' . (isset($instance['color1']) ? esc_js($instance['color1']) : 'FFFFFF') . '",
                color2: "' . (isset($instance['color2']) ? esc_js($instance['color2']) : '2B587A') . '",
                color3: "' . (isset($instance['color3']) ? esc_js($instance['color3']) : '5B7FA6') . '"
            }, ' . intval($group_id) . ');
        </script>';
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : __('Мы ВКонтакте', 'newscore');
        $group_id = isset($instance['group_id']) ? esc_attr($instance['group_id']) : '';
        $mode = isset($instance['mode']) ? esc_attr($instance['mode']) : 0;
        $width = isset($instance['width']) ? esc_attr($instance['width']) : 'auto';
        $height = isset($instance['height']) ? esc_attr($instance['height']) : '400';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo $title; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('group_id'); ?>"><?php _e('ID группы VK:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('group_id'); ?>" 
                   name="<?php echo $this->get_field_name('group_id'); ?>" type="text" 
                   value="<?php echo $group_id; ?>">
            <small>Например: 12345678</small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('mode'); ?>"><?php _e('Режим отображения:', 'newscore'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('mode'); ?>" 
                   name="<?php echo $this->get_field_name('mode'); ?>">
                <option value="0" <?php selected($mode, 0); ?>>Участники</option>
                <option value="1" <?php selected($mode, 1); ?>>Последние записи</option>
                <option value="2" <?php selected($mode, 2); ?>>Обсуждения</option>
                <option value="3" <?php selected($mode, 3); ?>>Аудиозаписи</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Ширина:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" 
                   name="<?php echo $this->get_field_name('width'); ?>" type="text" 
                   value="<?php echo $width; ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Высота:', 'newscore'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" 
                   name="<?php echo $this->get_field_name('height'); ?>" type="text" 
                   value="<?php echo $height; ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['group_id'] = absint($new_instance['group_id']);
        $instance['mode'] = absint($new_instance['mode']);
        $instance['width'] = sanitize_text_field($new_instance['width']);
        $instance['height'] = sanitize_text_field($new_instance['height']);
        return $instance;
    }
}

// Регистрация виджетов
function newscore_register_russian_widgets() {
    register_widget('Newscore_Yandex_Weather_Widget');
    register_widget('Newscore_Exchange_Rates_Widget');
    register_widget('Newscore_VK_Group_Widget');
}
add_action('widgets_init', 'newscore_register_russian_widgets');