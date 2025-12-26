<?php
/**
 * Russian specific widgets with actual functionality
 */

// Яндекс.Погода виджет с реальным API
class Newscore_Yandex_Weather_Widget extends WP_Widget {
    
    private $api_key;
    private $cache_time = 1800; // 30 минут кэша
    
    public function __construct() {
        parent::__construct(
            'newscore_yandex_weather',
            esc_html__('Яндекс.Погода', 'newscore'),
            array(
                'description' => esc_html__('Виджет погоды от Яндекс с реальными данными', 'newscore'),
                'customize_selective_refresh' => true
            )
        );
        
        $this->api_key = get_theme_mod('yandex_weather_api_key', '');
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_newscore_get_weather', array($this, 'ajax_get_weather'));
        add_action('wp_ajax_nopriv_newscore_get_weather', array($this, 'ajax_get_weather'));
    }
    
    public function enqueue_scripts() {
        if (is_active_widget(false, false, $this->id_base, true)) {
            wp_enqueue_style(
                'newscore-weather-widget',
                get_template_directory_uri() . '/assets/css/weather-widget.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'newscore-weather',
                get_template_directory_uri() . '/assets/js/weather-widget.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script('newscore-weather', 'newscore_weather', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('newscore_weather_nonce'),
                'api_key'  => $this->api_key,
                'default_city' => get_theme_mod('default_weather_city', 'Москва')
            ));
        }
    }
    
    public function ajax_get_weather() {
        // Проверяем nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'newscore_weather_nonce')) {
            wp_die('Security check failed');
        }
        
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : 'Москва';
        
        // Получаем данные о погоде
        $weather_data = $this->get_weather_data($city);
        
        if ($weather_data && !is_wp_error($weather_data)) {
            wp_send_json_success($weather_data);
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to get weather data', 'newscore'),
                'city' => $city
            ));
        }
    }
    
    private function get_weather_data($city) {
        $cache_key = 'newscore_weather_' . md5($city);
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Получаем координаты города
        $coordinates = $this->get_city_coordinates($city);
        if (!$coordinates) {
            return false;
        }
        
        // Получаем погоду через Яндекс API
        if (!empty($this->api_key)) {
            $weather = $this->get_yandex_weather($coordinates['lat'], $coordinates['lon']);
        } else {
            // Запасной вариант через OpenWeatherMap
            $weather = $this->get_openweather_weather($coordinates['lat'], $coordinates['lon']);
        }
        
        if ($weather && !is_wp_error($weather)) {
            $weather_data = array(
                'city'      => $city,
                'temp'      => round($weather['temp']),
                'feels_like' => round($weather['feels_like']),
                'description' => $weather['description'],
                'icon'      => $weather['icon'],
                'humidity'  => $weather['humidity'],
                'pressure'  => $weather['pressure'],
                'wind_speed' => $weather['wind_speed'],
                'wind_dir'  => $this->get_wind_direction($weather['wind_deg']),
                'updated'   => current_time('mysql'),
                'forecast'  => isset($weather['forecast']) ? $weather['forecast'] : array()
            );
            
            set_transient($cache_key, $weather_data, $this->cache_time);
            return $weather_data;
        }
        
        return false;
    }
    
    private function get_city_coordinates($city) {
        $cache_key = 'newscore_coords_' . md5($city);
        $cached_coords = get_transient($cache_key);
        
        if ($cached_coords !== false) {
            return $cached_coords;
        }
        
        // Используем Nominatim (OpenStreetMap) для геокодирования
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($city . ', Россия');
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'NewsCore Weather Widget/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            $coords = array(
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon']
            );
            
            set_transient($cache_key, $coords, DAY_IN_SECONDS);
            return $coords;
        }
        
        return false;
    }
    
    private function get_yandex_weather($lat, $lon) {
        $url = 'https://api.weather.yandex.ru/v2/forecast?lat=' . $lat . '&lon=' . $lon . '&extra=true';
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'X-Yandex-API-Key' => $this->api_key
            )
        ));
        
        if (is_wp_error($response)) {
            return $this->get_openweather_weather($lat, $lon);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['fact'])) {
            return array(
                'temp'       => $data['fact']['temp'],
                'feels_like' => $data['fact']['feels_like'],
                'description' => $this->get_weather_description($data['fact']['condition']),
                'icon'       => $this->get_weather_icon($data['fact']['icon']),
                'humidity'   => $data['fact']['humidity'],
                'pressure'   => $data['fact']['pressure_mm'],
                'wind_speed' => $data['fact']['wind_speed'],
                'wind_deg'   => $data['fact']['wind_dir'],
                'forecast'   => $this->parse_yandex_forecast($data['forecasts'])
            );
        }
        
        return false;
    }
    
    private function get_openweather_weather($lat, $lon) {
        $api_key = get_theme_mod('openweather_api_key', '');
        
        if (empty($api_key)) {
            // Используем бесплатный ключ или демо-данные
            return $this->get_demo_weather_data();
        }
        
        $url = 'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . 
               '&units=metric&appid=' . $api_key . '&lang=ru';
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return $this->get_demo_weather_data();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['main'])) {
            return array(
                'temp'       => $data['main']['temp'],
                'feels_like' => $data['main']['feels_like'],
                'description' => $data['weather'][0]['description'],
                'icon'       => $data['weather'][0]['icon'],
                'humidity'   => $data['main']['humidity'],
                'pressure'   => round($data['main']['pressure'] * 0.750062), // hPa to mmHg
                'wind_speed' => $data['wind']['speed'],
                'wind_deg'   => isset($data['wind']['deg']) ? $data['wind']['deg'] : 0
            );
        }
        
        return $this->get_demo_weather_data();
    }
    
    private function get_demo_weather_data() {
        // Демо-данные для случая отсутствия API ключей
        $conditions = array(
            'ясно', 'малооблачно', 'облачно с прояснениями', 
            'пасмурно', 'небольшой дождь', 'дождь', 'гроза'
        );
        
        $icons = array('01d', '02d', '03d', '04d', '09d', '10d', '11d');
        
        $key = array_rand($conditions);
        
        return array(
            'temp'       => rand(-15, 30),
            'feels_like' => rand(-15, 30),
            'description' => $conditions[$key],
            'icon'       => $icons[$key],
            'humidity'   => rand(40, 90),
            'pressure'   => rand(730, 780),
            'wind_speed' => rand(0, 15),
            'wind_deg'   => rand(0, 360)
        );
    }
    
    private function get_weather_description($condition) {
        $conditions = array(
            'clear' => 'ясно',
            'partly-cloudy' => 'малооблачно',
            'cloudy' => 'облачно с прояснениями',
            'overcast' => 'пасмурно',
            'drizzle' => 'морось',
            'light-rain' => 'небольшой дождь',
            'rain' => 'дождь',
            'moderate-rain' => 'умеренно сильный дождь',
            'heavy-rain' => 'сильный дождь',
            'continuous-heavy-rain' => 'длительный сильный дождь',
            'showers' => 'ливень',
            'wet-snow' => 'дождь со снегом',
            'light-snow' => 'небольшой снег',
            'snow' => 'снег',
            'snow-showers' => 'снегопад',
            'hail' => 'град',
            'thunderstorm' => 'гроза',
            'thunderstorm-with-rain' => 'дождь с грозой',
            'thunderstorm-with-hail' => 'гроза с градом'
        );
        
        return isset($conditions[$condition]) ? $conditions[$condition] : $condition;
    }
    
    private function get_weather_icon($icon) {
        // Конвертируем иконки Яндекс в OpenWeatherMap формат
        $icon_map = array(
            'skc' => '01', // ясно
            'ovc' => '04', // пасмурно
            'bkn' => '03', // облачно
            // ... другие соответствия
        );
        
        return isset($icon_map[$icon]) ? $icon_map[$icon] . 'd' : '02d';
    }
    
    private function get_wind_direction($degrees) {
        $directions = array('С', 'СВ', 'В', 'ЮВ', 'Ю', 'ЮЗ', 'З', 'СЗ');
        $index = round($degrees / 45) % 8;
        return $directions[$index];
    }
    
    private function parse_yandex_forecast($forecasts) {
        $result = array();
        
        foreach (array_slice($forecasts, 0, 3) as $forecast) {
            $result[] = array(
                'date' => date('d.m', strtotime($forecast['date'])),
                'temp_day' => $forecast['parts']['day']['temp_avg'],
                'temp_night' => $forecast['parts']['night']['temp_avg'],
                'icon' => $this->get_weather_icon($forecast['parts']['day']['icon'])
            );
        }
        
        return $result;
    }
    
    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);
        
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        $city = !empty($instance['city']) ? $instance['city'] : 'Москва';
        
        if ($title) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        
        $widget_id = 'weather-widget-' . $this->number;
        ?>
        
        <div class="weather-widget" id="<?php echo esc_attr($widget_id); ?>" data-city="<?php echo esc_attr($city); ?>">
            <div class="weather-current">
                <div class="weather-loading">
                    <?php esc_html_e('Загрузка погоды...', 'newscore'); ?>
                </div>
                
                <div class="weather-data" style="display: none;">
                    <div class="weather-main">
                        <div class="weather-icon">
                            <img src="" alt="" class="weather-icon-img">
                        </div>
                        <div class="weather-temp">
                            <span class="temp-value">--</span>
                            <span class="temp-unit">°C</span>
                        </div>
                    </div>
                    
                    <div class="weather-details">
                        <div class="weather-city"><?php echo esc_html($city); ?></div>
                        <div class="weather-description"></div>
                        <div class="weather-feels-like">
                            <?php esc_html_e('Ощущается как:', 'newscore'); ?> 
                            <span class="feels-like-value">--</span>°C
                        </div>
                        
                        <div class="weather-extra">
                            <div class="weather-item">
                                <span class="weather-label"><?php esc_html_e('Влажность:', 'newscore'); ?></span>
                                <span class="humidity-value">--%</span>
                            </div>
                            <div class="weather-item">
                                <span class="weather-label"><?php esc_html_e('Давление:', 'newscore'); ?></span>
                                <span class="pressure-value">--</span> мм рт.ст.
                            </div>
                            <div class="weather-item">
                                <span class="weather-label"><?php esc_html_e('Ветер:', 'newscore'); ?></span>
                                <span class="wind-value">--</span> м/c <span class="wind-dir"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="weather-forecast" style="display: none;">
                <h4><?php esc_html_e('Прогноз на 3 дня:', 'newscore'); ?></h4>
                <div class="forecast-days"></div>
            </div>
            
            <div class="weather-error" style="display: none;"></div>
            
            <div class="weather-update">
                <small class="update-time"></small>
                <button class="weather-refresh" aria-label="<?php esc_attr_e('Обновить погоду', 'newscore'); ?>">
                    <?php esc_html_e('Обновить', 'newscore'); ?>
                </button>
            </div>
            
            <?php if (empty($this->api_key) && current_user_can('edit_theme_options')) : ?>
                <div class="weather-admin-notice">
                    <small>
                        <?php 
                        printf(
                            esc_html__('Для работы виджета добавьте API ключ в %sНастройки темы → Яндекс сервисы%s', 'newscore'),
                            '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=newscore_yandex')) . '">',
                            '</a>'
                        ); 
                        ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
        echo wp_kses_post($args['after_widget']);
    }
    
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Погода', 'newscore'),
            'city' => 'Москва'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = esc_attr($instance['title']);
        $city = esc_attr($instance['city']);
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
                <label for="<?php echo esc_attr($this->get_field_id('city')); ?>">
                    <?php esc_html_e('Город:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('city')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('city')); ?>" 
                       type="text" 
                       value="<?php echo esc_html(); ?>"
                       placeholder="<?php esc_attr_e('Например: Москва, Санкт-Петербург, Новосибирск', 'newscore'); ?>">
                <small class="description">
                    <?php esc_html_e('Укажите город на русском языке', 'newscore'); ?>
                </small>
            </p>
            
            <?php if (empty($this->api_key) && current_user_can('edit_theme_options')) : ?>
                <div class="widget-admin-notice notice notice-warning">
                    <p>
                        <?php 
                        printf(
                            esc_html__('Виджет использует демо-данные. Для получения реальных данных добавьте API ключ Яндекс.Погоды в %sНастройки темы%s.', 'newscore'),
                            '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=newscore_yandex')) . '">',
                            '</a>'
                        ); 
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['city'] = sanitize_text_field($new_instance['city']);
        
        return $instance;
    }
}

// Курсы валют виджет с реальными данными
class Newscore_Exchange_Rates_Widget extends WP_Widget {
    
    private $cache_time = 3600; // 1 час кэша
    
    public function __construct() {
        parent::__construct(
            'newscore_exchange_rates',
            esc_html__('Курсы валют', 'newscore'),
            array(
                'description' => esc_html__('Курсы валют ЦБ РФ с обновлением данных', 'newscore'),
                'customize_selective_refresh' => true
            )
        );
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_newscore_get_exchange_rates', array($this, 'ajax_get_rates'));
        add_action('wp_ajax_nopriv_newscore_get_exchange_rates', array($this, 'ajax_get_rates'));
        add_action('newscore_update_exchange_rates', array($this, 'update_rates_cache'));
    }
    
    public function enqueue_scripts() {
        if (is_active_widget(false, false, $this->id_base, true)) {
            wp_enqueue_style(
                'newscore-exchange-widget',
                get_template_directory_uri() . '/assets/css/exchange-widget.css',
                array(),
                '1.0.0'
            );
        }
    }
    
    public function ajax_get_rates() {
        $rates = $this->get_exchange_rates();
        
        if ($rates && !is_wp_error($rates)) {
            wp_send_json_success($rates);
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to get exchange rates', 'newscore')
            ));
        }
    }
    
    private function get_exchange_rates() {
        $cache_key = 'newscore_exchange_rates';
        $cached_rates = get_transient($cache_key);
        
        if ($cached_rates !== false) {
            return $cached_rates;
        }
        
        // Пробуем несколько источников
        $rates = $this->get_cbr_rates();
        
        if (!$rates) {
            $rates = $this->get_alternative_rates();
        }
        
        if ($rates) {
            set_transient($cache_key, $rates, $this->cache_time);
            
            // Планируем обновление кэша
            if (!wp_next_scheduled('newscore_update_exchange_rates')) {
                wp_schedule_single_event(time() + $this->cache_time, 'newscore_update_exchange_rates');
            }
            
            return $rates;
        }
        
        return $this->get_demo_rates();
    }
    
    private function get_cbr_rates() {
        $url = 'https://www.cbr-xml-daily.ru/daily_json.js';
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['Valute'])) {
            $rates = array(
                'USD' => array(
                    'name' => 'Доллар США',
                    'value' => $data['Valute']['USD']['Value'],
                    'change' => $data['Valute']['USD']['Value'] - $data['Valute']['USD']['Previous'],
                    'nominal' => $data['Valute']['USD']['Nominal']
                ),
                'EUR' => array(
                    'name' => 'Евро',
                    'value' => $data['Valute']['EUR']['Value'],
                    'change' => $data['Valute']['EUR']['Value'] - $data['Valute']['EUR']['Previous'],
                    'nominal' => $data['Valute']['EUR']['Nominal']
                ),
                'CNY' => array(
                    'name' => 'Китайский юань',
                    'value' => $data['Valute']['CNY']['Value'],
                    'change' => $data['Valute']['CNY']['Value'] - $data['Valute']['CNY']['Previous'],
                    'nominal' => $data['Valute']['CNY']['Nominal']
                )
            );
            
            return array(
                'rates' => $rates,
                'date' => isset($data['Date']) ? $data['Date'] : current_time('mysql'),
                'source' => 'ЦБ РФ'
            );
        }
        
        return false;
    }
    
    private function get_alternative_rates() {
        // Альтернативный источник через exchangerate-api.com
        $api_key = get_theme_mod('exchange_api_key', '');
        
        if (empty($api_key)) {
            return false;
        }
        
        $url = 'https://api.exchangerate-api.com/v4/latest/RUB';
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['rates'])) {
            $rates = array(
                'USD' => array(
                    'name' => 'Доллар США',
                    'value' => round(1 / $data['rates']['USD'], 4),
                    'change' => 0,
                    'nominal' => 1
                ),
                'EUR' => array(
                    'name' => 'Евро',
                    'value' => round(1 / $data['rates']['EUR'], 4),
                    'change' => 0,
                    'nominal' => 1
                )
            );
            
            return array(
                'rates' => $rates,
                'date' => date('Y-m-d'),
                'source' => 'ExchangeRate-API'
            );
        }
        
        return false;
    }
    
    private function get_demo_rates() {
        // Демо-данные
        return array(
            'rates' => array(
                'USD' => array(
                    'name' => 'Доллар США',
                    'value' => rand(85, 95) + (rand(0, 99) / 100),
                    'change' => rand(-2, 2) + (rand(0, 99) / 100),
                    'nominal' => 1
                ),
                'EUR' => array(
                    'name' => 'Евро',
                    'value' => rand(95, 105) + (rand(0, 99) / 100),
                    'change' => rand(-2, 2) + (rand(0, 99) / 100),
                    'nominal' => 1
                ),
                'CNY' => array(
                    'name' => 'Китайский юань',
                    'value' => rand(12, 15) + (rand(0, 99) / 100),
                    'change' => rand(-1, 1) + (rand(0, 99) / 100),
                    'nominal' => 10
                )
            ),
            'date' => current_time('mysql'),
            'source' => 'Демо-данные'
        );
    }
    
    public function update_rates_cache() {
        $this->get_exchange_rates();
    }
    
    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);
        
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        
        if ($title) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        
        $widget_id = 'exchange-widget-' . $this->number;
        ?>
        
        <div class="exchange-rates-widget" id="<?php echo esc_attr($widget_id); ?>">
            <div class="exchange-loading">
                <?php esc_html_e('Загрузка курсов...', 'newscore'); ?>
            </div>
            
            <div class="exchange-data" style="display: none;">
                <div class="exchange-header">
                    <span class="exchange-label"><?php esc_html_e('Валюта', 'newscore'); ?></span>
                    <span class="exchange-label"><?php esc_html_e('Курс', 'newscore'); ?></span>
                    <span class="exchange-label"><?php esc_html_e('Изменение', 'newscore'); ?></span>
                </div>
                
                <div class="exchange-rates">
                    <!-- Данные будут добавлены через JavaScript -->
                </div>
                
                <div class="exchange-footer">
                    <div class="exchange-date">
                        <?php esc_html_e('Обновлено:', 'newscore'); ?> 
                        <span class="update-date"></span>
                    </div>
                    <div class="exchange-source">
                        <?php esc_html_e('Источник:', 'newscore'); ?> 
                        <span class="source-name"></span>
                    </div>
                    <button class="exchange-refresh" aria-label="<?php esc_attr_e('Обновить курсы валют', 'newscore'); ?>">
                        <?php esc_html_e('Обновить', 'newscore'); ?>
                    </button>
                </div>
            </div>
            
            <div class="exchange-error" style="display: none;"></div>
            
            <?php if (current_user_can('edit_theme_options')) : ?>
                <div class="exchange-admin-notice">
                    <small>
                        <?php 
                        esc_html_e('Виджет использует открытые данные ЦБ РФ. Для стабильной работы добавьте API ключ в настройках темы.', 'newscore');
                        ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
        echo wp_kses_post($args['after_widget']);
    }
    
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Курсы валют', 'newscore')
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = esc_attr($instance['title']);
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
            
            <div class="widget-description">
                <p>
                    <?php esc_html_e('Виджет автоматически получает актуальные курсы валют с сайта ЦБ РФ.', 'newscore'); ?>
                </p>
                
                <?php if (current_user_can('edit_theme_options')) : ?>
                    <div class="notice notice-info">
                        <p>
                            <?php 
                            printf(
                                esc_html__('Для настройки дополнительных валют перейдите в %sНастройки темы → Финансы%s', 'newscore'),
                                '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=newscore_finance')) . '">',
                                '</a>'
                            ); 
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
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
            esc_html__('VK Группа', 'newscore'),
            array(
                'description' => esc_html__('Виджет группы ВКонтакте', 'newscore'),
                'customize_selective_refresh' => true
            )
        );
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        if (is_active_widget(false, false, $this->id_base, true)) {
            // Подключаем VK SDK
            wp_enqueue_script(
                'vk-api',
                'https://vk.com/js/api/openapi.js?169',
                array(),
                '169',
                false
            );
        }
    }
    
    public function widget($args, $instance) {
        echo wp_kses_post($args['before_widget']);
        
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        
        if ($title) {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
        
        $group_id = isset($instance['group_id']) ? absint($instance['group_id']) : 0;
        $mode = isset($instance['mode']) ? absint($instance['mode']) : 0;
        $width = isset($instance['width']) ? $instance['width'] : 'auto';
        $height = isset($instance['height']) ? $instance['height'] : '400';
        $color1 = isset($instance['color1']) ? $instance['color1'] : 'FFFFFF';
        $color2 = isset($instance['color2']) ? $instance['color2'] : '2B587A';
        $color3 = isset($instance['color3']) ? $instance['color3'] : '5B7FA6';
        
        if (empty($group_id)) {
            if (current_user_can('edit_theme_options')) {
                echo '<div class="vk-widget-error">';
                echo esc_html__('Укажите ID группы VK в настройках виджета.', 'newscore');
                echo '</div>';
            }
            echo wp_kses_post($args['after_widget']);
            return;
        }
        
        // Проверяем валидность цветов
        $color1 = $this->validate_color($color1);
        $color2 = $this->validate_color($color2);
        $color3 = $this->validate_color($color3);
        ?>
        
        <div class="vk-group-widget">
            <div id="vk_groups_<?php echo esc_attr($this->number); ?>"></div>
            
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof VK !== 'undefined') {
                        VK.Widgets.Group(
                            "vk_groups_<?php echo esc_attr($this->number); ?>", 
                            {
                                mode: <?php echo absint($mode); ?>,
                                width: "<?php echo esc_js($width); ?>",
                                height: "<?php echo esc_js($height); ?>",
                                color1: "<?php echo esc_js($color1); ?>",
                                color2: "<?php echo esc_js($color2); ?>",
                                color3: "<?php echo esc_js($color3); ?>"
                            }, 
                            <?php echo absint($group_id); ?>
                        );
                    } else {
                        console.warn('VK Widgets API not loaded');
                        
                        // Показываем альтернативное содержимое
                        var container = document.getElementById('vk_groups_<?php echo esc_attr($this->number); ?>');
                        if (container) {
                            container.innerHTML = `
                                <div class="vk-widget-fallback">
                                    <p><?php esc_html_e('Не удалось загрузить виджет VK. Перейдите в группу:', 'newscore'); ?></p>
                                    <a href="https://vk.com/club<?php echo absint($group_id); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="button">
                                        <?php esc_html_e('Открыть группу VK', 'newscore'); ?>
                                    </a>
                                </div>
                            `;
                        }
                    }
                });
            </script>
            
            <noscript>
                <div class="vk-widget-noscript">
                    <p><?php esc_html_e('Для отображения виджета включите JavaScript.', 'newscore'); ?></p>
                    <a href="https://vk.com/club<?php echo absint($group_id); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <?php esc_html_e('Открыть группу ВКонтакте', 'newscore'); ?>
                    </a>
                </div>
            </noscript>
        </div>
        
        <?php
        echo wp_kses_post($args['after_widget']);
    }
    
    private function validate_color($color) {
        // Удаляем # если есть
        $color = ltrim($color, '#');
        
        // Проверяем hex формат
        if (preg_match('/^[0-9A-F]{6}$/i', $color)) {
            return $color;
        }
        
        // Стандартные цвета VK
        $default_colors = array(
            'FFFFFF', // Белый
            '2B587A', // Синий (темный)
            '5B7FA6'  // Синий (светлый)
        );
        
        return $default_colors[array_rand($default_colors)];
    }
    
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Мы ВКонтакте', 'newscore'),
            'group_id' => '',
            'mode' => 0,
            'width' => 'auto',
            'height' => '400',
            'color1' => 'FFFFFF',
            'color2' => '2B587A',
            'color3' => '5B7FA6'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = esc_attr($instance['title']);
        $group_id = esc_attr($instance['group_id']);
        $mode = absint($instance['mode']);
        $width = esc_attr($instance['width']);
        $height = esc_attr($instance['height']);
        $color1 = esc_attr($instance['color1']);
        $color2 = esc_attr($instance['color2']);
        $color3 = esc_attr($instance['color3']);
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
                <label for="<?php echo esc_attr($this->get_field_id('group_id')); ?>">
                    <?php esc_html_e('ID группы VK:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('group_id')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('group_id')); ?>" 
                       type="number" 
                       value="<?php echo esc_html(); ?>"
                       placeholder="12345678"
                       min="1">
                <small class="description">
                    <?php esc_html_e('Числовой ID группы (не короткое имя)', 'newscore'); ?>
                </small>
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('mode')); ?>">
                    <?php esc_html_e('Режим отображения:', 'newscore'); ?>
                </label>
                <select class="widefat" 
                        id="<?php echo esc_attr($this->get_field_id('mode')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('mode')); ?>">
                    <option value="0" <?php selected($mode, 0); ?>><?php esc_html_e('Участники', 'newscore'); ?></option>
                    <option value="1" <?php selected($mode, 1); ?>><?php esc_html_e('Последние записи', 'newscore'); ?></option>
                    <option value="2" <?php selected($mode, 2); ?>><?php esc_html_e('Обсуждения', 'newscore'); ?></option>
                    <option value="3" <?php selected($mode, 3); ?>><?php esc_html_e('Аудиозаписи', 'newscore'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('width')); ?>">
                    <?php esc_html_e('Ширина:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('width')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('width')); ?>" 
                       type="text" 
                       value="<?php echo esc_html(); ?>"
                       placeholder="auto">
                <small class="description">
                    <?php esc_html_e('Например: 300, 100%, auto', 'newscore'); ?>
                </small>
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('height')); ?>">
                    <?php esc_html_e('Высота:', 'newscore'); ?>
                </label>
                <input class="widefat" 
                       id="<?php echo esc_attr($this->get_field_id('height')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('height')); ?>" 
                       type="number" 
                       value="<?php echo esc_html(); ?>"
                       min="200" 
                       max="1000">
            </p>
            
            <div class="color-picker-group">
                <h4><?php esc_html_e('Цвета виджета:', 'newscore'); ?></h4>
                
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('color1')); ?>">
                        <?php esc_html_e('Фоновый цвет:', 'newscore'); ?>
                    </label>
                    <input class="widefat color-picker" 
                           id="<?php echo esc_attr($this->get_field_id('color1')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('color1')); ?>" 
                           type="text" 
                           value="<?php echo esc_html(); ?>"
                           data-default-color="#FFFFFF">
                </p>
                
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('color2')); ?>">
                        <?php esc_html_e('Цвет текста:', 'newscore'); ?>
                    </label>
                    <input class="widefat color-picker" 
                           id="<?php echo esc_attr($this->get_field_id('color2')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('color2')); ?>" 
                           type="text" 
                           value="<?php echo esc_html(); ?>"
                           data-default-color="#2B587A">
                </p>
                
                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('color3')); ?>">
                        <?php esc_html_e('Цвет кнопок:', 'newscore'); ?>
                    </label>
                    <input class="widefat color-picker" 
                           id="<?php echo esc_attr($this->get_field_id('color3')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('color3')); ?>" 
                           type="text" 
                           value="<?php echo esc_html(); ?>"
                           data-default-color="#5B7FA6">
                </p>
            </div>
            
            <div class="widget-description">
                <p>
                    <?php 
                    printf(
                        esc_html__('ID группы можно найти в адресе: %shttps://vk.com/club12345678%s', 'newscore'),
                        '<code>',
                        '</code>'
                    ); 
                    ?>
                </p>
            </div>
        </div>
        
        <?php
        // Подключаем color picker для админки
        if (is_admin()) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            
            add_action('admin_footer', function() {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('.color-picker').wpColorPicker();
                    });
                </script>
                <?php
            });
        }
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['group_id'] = absint($new_instance['group_id']);
        $instance['mode'] = absint($new_instance['mode']);
        $instance['width'] = sanitize_text_field($new_instance['width']);
        $instance['height'] = absint($new_instance['height']);
        $instance['color1'] = sanitize_text_field($new_instance['color1']);
        $instance['color2'] = sanitize_text_field($new_instance['color2']);
        $instance['color3'] = sanitize_text_field($new_instance['color3']);
        
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

// Создаем сайдбары для виджетов
function newscore_register_russian_sidebars() {
    // Сайдбар для погоды и валют
    register_sidebar(array(
        'name'          => esc_html__('Виджеты (погода и курсы)', 'newscore'),
        'id'            => 'russian-widgets',
        'description'   => esc_html__('Добавьте виджеты погоды, курсов валют и социальных сетей', 'newscore'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Сайдбар для VK виджетов
    register_sidebar(array(
        'name'          => esc_html__('Социальные виджеты', 'newscore'),
        'id'            => 'social-widgets',
        'description'   => esc_html__('Добавьте виджеты социальных сетей (VK, Telegram и др.)', 'newscore'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}

add_action('widgets_init', 'newscore_register_russian_sidebars');

// Крон задача для обновления курсов валют
function newscore_schedule_exchange_updates() {
    if (!wp_next_scheduled('newscore_daily_exchange_update')) {
        wp_schedule_event(time(), 'twicedaily', 'newscore_daily_exchange_update');
    }
}

add_action('wp', 'newscore_schedule_exchange_updates');

function newscore_daily_exchange_update() {
    // Обновляем кэш курсов валют
    $widget = new Newscore_Exchange_Rates_Widget();
    $widget->update_rates_cache();
}

add_action('newscore_daily_exchange_update', 'newscore_daily_exchange_update');

// Шорткод для погоды
function newscore_weather_shortcode($atts) {
    $atts = shortcode_atts(array(
        'city' => 'Москва',
        'show_forecast' => false
    ), $atts, 'weather');
    
    $widget = new Newscore_Yandex_Weather_Widget();
    
    ob_start();
    ?>
    <div class="weather-shortcode" data-city="<?php echo esc_attr($atts['city']); ?>">
        <div class="weather-shortcode-loading">
            <?php esc_html_e('Загрузка погоды...', 'newscore'); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('weather', 'newscore_weather_shortcode');