/**
 * Russian Services Integration - Secure Version 2.0.1
 * Безопасная интеграция с российскими сервисами
 */

(function($) {
    'use strict';
    
    // Проверка наличия объекта
    if (typeof newscore_ru_secure === 'undefined') {
        console.warn('Конфигурация российских сервисов не загружена');
        return;
    }
    
    const RussianServices = {
        
        // Конфигурация
        config: {
            apiEndpoints: {
                weather: 'https://api.weather.yandex.ru/v2/forecast',
                currency: 'https://www.cbr-xml-daily.ru/daily_json.js',
                geolocation: 'https://api.sypexgeo.net/json/',
                yandexMaps: 'https://api-maps.yandex.ru/2.1/'
            },
            cacheDuration: {
                weather: 1800000, // 30 минут
                currency: 300000, // 5 минут
                location: 86400000 // 24 часа
            }
        },
        
        // Инициализация
        init: function() {
            this.loadWeather();
            this.loadCurrencyRates();
            this.initYandexMaps();
            this.detectUserRegion();
            this.initRussianHolidays();
            this.optimizeForRussianBrowsers();
        },
        
        // Погода от Яндекс (безопасная)
        loadWeather: function() {
            const $widgets = $('.weather-widget');
            
            if (!$widgets.length || !newscore_ru_secure.yandex_api_key) {
                return;
            }
            
            $widgets.each((index, widget) => {
                const $widget = $(widget);
                const city = $widget.data('city') || newscore_ru_secure.weather_city;
                const coords = this.getCityCoordinates(city);
                
                // Проверяем кэш
                const cacheKey = 'weather_' + city;
                const cached = localStorage.getItem(cacheKey);
                
                if (cached) {
                    const data = JSON.parse(cached);
                    if (Date.now() - data.timestamp < this.config.cacheDuration.weather) {
                        this.renderWeather($widget, data.weather);
                        return;
                    }
                }
                
                // Запрос через сервер (безопасно)
                $.ajax({
                    url: newscore_ru_secure.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'get_yandex_weather',
                        city: city,
                        lat: coords.lat,
                        lon: coords.lon,
                        nonce: newscore_ru_secure.nonce
                    }
                })
                .done((response) => {
                    if (response.success) {
                        this.renderWeather($widget, response.data);
                        
                        // Кэшируем
                        localStorage.setItem(cacheKey, JSON.stringify({
                            weather: response.data,
                            timestamp: Date.now()
                        }));
                    } else {
                        this.renderWeatherError($widget);
                    }
                })
                .fail(() => {
                    this.renderWeatherError($widget);
                });
            });
        },
        
        // Курсы валют ЦБ РФ
        loadCurrencyRates: function() {
            const $widgets = $('.exchange-rates-widget');
            
            if (!$widgets.length) return;
            
            // Проверяем кэш
            const cacheKey = 'currency_rates';
            const cached = localStorage.getItem(cacheKey);
            
            if (cached) {
                const data = JSON.parse(cached);
                if (Date.now() - data.timestamp < this.config.cacheDuration.currency) {
                    this.renderCurrencyRates($widgets, data.rates);
                    return;
                }
            }
            
            // Запрос курсов
            $.ajax({
                url: this.config.apiEndpoints.currency,
                method: 'GET',
                dataType: 'json',
                timeout: 5000
            })
            .done((data) => {
                if (data && data.Valute) {
                    this.renderCurrencyRates($widgets, data.Valute);
                    
                    // Кэшируем
                    localStorage.setItem(cacheKey, JSON.stringify({
                        rates: data.Valute,
                        timestamp: Date.now()
                    }));
                } else {
                    this.renderCurrencyError($widgets);
                }
            })
            .fail(() => {
                this.renderCurrencyError($widgets);
            });
        },
        
        // Яндекс.Карты
        initYandexMaps: function() {
            const $maps = $('[data-yandex-map]');
            
            if (!$maps.length) return;
            
            // Загружаем API Яндекс.Карт
            if (typeof ymaps === 'undefined') {
                this.loadYandexMapsAPI().then(() => {
                    this.renderMaps($maps);
                });
            } else {
                this.renderMaps($maps);
            }
        },
        
        // Определение региона пользователя
        detectUserRegion: function() {
            const cacheKey = 'user_location';
            const cached = localStorage.getItem(cacheKey);
            
            if (cached) {
                const data = JSON.parse(cached);
                if (Date.now() - data.timestamp < this.config.cacheDuration.location) {
                    this.updateRegionalContent(data.location);
                    return;
                }
            }
            
            // Запрос геолокации через наш сервер
            $.ajax({
                url: newscore_ru_secure.ajaxurl,
                method: 'POST',
                data: {
                    action: 'detect_user_region',
                    nonce: newscore_ru_secure.nonce
                }
            })
            .done((response) => {
                if (response.success) {
                    const location = response.data;
                    
                    // Сохраняем в кэш
                    localStorage.setItem(cacheKey, JSON.stringify({
                        location: location,
                        timestamp: Date.now()
                    }));
                    
                    // Обновляем контент
                    this.updateRegionalContent(location);
                    
                    // Сохраняем в cookie
                    document.cookie = `user_region=${encodeURIComponent(location.region)}; path=/; max-age=86400`;
                    document.cookie = `user_city=${encodeURIComponent(location.city)}; path=/; max-age=86400`;
                }
            })
            .fail(() => {
                // Используем настройки по умолчанию
                this.updateRegionalContent({
                    country: 'RU',
                    region: 'Moscow',
                    city: 'Москва'
                });
            });
        },
        
        // Российские праздники
        initRussianHolidays: function() {
            const today = new Date();
            const month = today.getMonth() + 1;
            const day = today.getDate();
            
            const holidays = {
                '1-1': 'С Новым годом! 🎄',
                '1-7': 'С Рождеством Христовым! ✨',
                '2-23': 'С Днём защитника Отечества! 🎖️',
                '3-8': 'С Международным женским днём! 💐',
                '5-1': 'С Праздником Весны и Труда! 🌸',
                '5-9': 'С Днём Победы! 🎖️',
                '6-12': 'С Днём России! 🇷🇺',
                '11-4': 'С Днём народного единства! 🤝'
            };
            
            const key = `${month}-${day}`;
            
            if (holidays[key]) {
                const $banner = $('.holiday-banner');
                if ($banner.length) {
                    $banner.text(holidays[key]).show();
                }
            }
        },
        
        // Оптимизация для российских браузеров
        optimizeForRussianBrowsers: function() {
            const ua = navigator.userAgent;
            
            // Яндекс.Браузер
            if (ua.includes('YaBrowser')) {
                $('body').addClass('yandex-browser');
                
                // Оптимизация для Турбо-режима
                if (window.outerWidth === 0) {
                    this.lazyLoadImages();
                }
            }
            
            // Mail.ru Амиго
            if (ua.includes('Amigo')) {
                $('body').addClass('amigo-browser');
            }
            
            // UC Browser
            if (ua.includes('UCBrowser')) {
                $('body').addClass('uc-browser');
            }
        },
        
        // Вспомогательные методы
        getCityCoordinates: function(city) {
            const cities = {
                'Москва': { lat: 55.7558, lon: 37.6173 },
                'Санкт-Петербург': { lat: 59.9343, lon: 30.3351 },
                'Новосибирск': { lat: 55.0084, lon: 82.9357 },
                'Екатеринбург': { lat: 56.8389, lon: 60.6057 },
                'Казань': { lat: 55.7961, lon: 49.1064 },
                'Нижний Новгород': { lat: 56.3269, lon: 44.0065 },
                'Челябинск': { lat: 55.1644, lon: 61.4368 },
                'Самара': { lat: 53.1959, lon: 50.1002 },
                'Омск': { lat: 54.9893, lon: 73.3682 },
                'Ростов-на-Дону': { lat: 47.222, lon: 39.718 },
                'Уфа': { lat: 54.7348, lon: 55.9578 },
                'Красноярск': { lat: 56.0153, lon: 92.8932 },
                'Воронеж': { lat: 51.672, lon: 39.1843 },
                'Пермь': { lat: 58.0105, lon: 56.2502 },
                'Волгоград': { lat: 48.708, lon: 44.5133 }
            };
            
            return cities[city] || cities['Москва'];
        },
        
        renderWeather: function($widget, data) {
            const html = `
                <div class="weather-info">
                    <div class="weather-header">
                        <div class="weather-city">${data.city}</div>
                        <div class="weather-temp">${data.temp}°C</div>
                    </div>
                    <div class="weather-condition">${data.condition}</div>
                    <div class="weather-details">
                        <span class="weather-wind">Ветер: ${data.wind_speed} м/с</span>
                        <span class="weather-humidity">Влажность: ${data.humidity}%</span>
                    </div>
                </div>
            `;
            
            $widget.html(html).removeClass('loading');
        },
        
        renderWeatherError: function($widget) {
            $widget.html(`
                <div class="weather-error">
                    <div class="error-icon">☁️</div>
                    <div class="error-text">Не удалось загрузить погоду</div>
                </div>
            `).removeClass('loading');
        },
        
        renderCurrencyRates: function($widgets, rates) {
            const currencies = ['USD', 'EUR', 'CNY'];
            let html = '<div class="exchange-rates">';
            
            currencies.forEach(code => {
                if (rates[code]) {
                    const rate = rates[code];
                    const change = rate.Value - rate.Previous;
                    const changeClass = change > 0 ? 'positive' : 'negative';
                    const changeSymbol = change > 0 ? '↑' : '↓';
                    
                    html += `
                        <div class="exchange-rate">
                            <div class="currency-code">${code}/₽</div>
                            <div class="currency-rate">${rate.Value.toFixed(2)}</div>
                            <div class="currency-change ${changeClass}">
                                ${changeSymbol} ${Math.abs(change).toFixed(2)}
                            </div>
                        </div>
                    `;
                }
            });
            
            html += '</div>';
            
            $widgets.html(html).removeClass('loading');
        },
        
        renderCurrencyError: function($widgets) {
            $widgets.html(`
                <div class="currency-error">
                    <div class="error-icon">💱</div>
                    <div class="error-text">Курсы временно недоступны</div>
                </div>
            `).removeClass('loading');
        },
        
        loadYandexMapsAPI: function() {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = this.config.apiEndpoints.yandexMaps + '?lang=ru_RU&load=package.full';
                script.async = true;
                
                script.onload = () => resolve();
                script.onerror = () => reject();
                
                document.head.appendChild(script);
            });
        },
        
        renderMaps: function($maps) {
            if (typeof ymaps === 'undefined') return;
            
            ymaps.ready(() => {
                $maps.each((index, mapElement) => {
                    const $map = $(mapElement);
                    const mapId = $map.attr('id') || `yandex-map-${index}`;
                    $map.attr('id', mapId);
                    
                    const lat = parseFloat($map.data('lat')) || 55.7558;
                    const lon = parseFloat($map.data('lon')) || 37.6173;
                    const zoom = parseInt($map.data('zoom')) || 12;
                    const title = $map.data('title') || 'Местоположение';
                    
                    try {
                        const map = new ymaps.Map(mapId, {
                            center: [lat, lon],
                            zoom: zoom,
                            controls: ['zoomControl', 'fullscreenControl']
                        });
                        
                        const placemark = new ymaps.Placemark([lat, lon], {
                            balloonContent: title
                        }, {
                            preset: 'islands#icon',
                            iconColor: '#ff0000'
                        });
                        
                        map.geoObjects.add(placemark);
                        
                        // Центрируем карту
                        map.setBounds(map.geoObjects.getBounds(), {
                            checkZoomRange: true
                        });
                        
                    } catch (error) {
                        console.error('Ошибка инициализации карты:', error);
                        $map.html('<div class="map-error">Не удалось загрузить карту</div>');
                    }
                });
            });
        },
        
        updateRegionalContent: function(location) {
            $('.regional-content').each((index, element) => {
                const $element = $(element);
                const regions = $element.data('regions');
                
                if (regions) {
                    const regionList = regions.split(',');
                    if (regionList.includes('all') || regionList.includes(location.region)) {
                        $element.show();
                    } else {
                        $element.hide();
                    }
                }
            });
            
            // Обновляем текст для региона
            $('[data-region-text]').each((index, element) => {
                const $element = $(element);
                const region = $element.data('region-text');
                
                if (region === location.region || region === 'all') {
                    $element.show();
                }
            });
        },
        
        lazyLoadImages: function() {
            $('img[data-src]').each((index, img) => {
                const $img = $(img);
                const src = $img.data('src');
                
                if (src) {
                    $img.attr('src', src).removeAttr('data-src');
                }
            });
        },
        
        // Отслеживание соц. действий
        trackSocialAction: function(network, action) {
            if (typeof ym !== 'undefined') {
                ym(newscore_ru_secure.yandex_metrika_id, 'reachGoal', `social_${network}_${action}`);
            }
            
            if (typeof gtag !== 'undefined') {
                gtag('event', 'social_action', {
                    event_category: 'social',
                    event_label: `${network}_${action}`,
                    value: 1
                });
            }
        },
        
        // Инициализация VK виджетов
        initVKWidgets: function() {
            if (typeof VK === 'undefined') return;
            
            // Комментарии
            $('[data-vk-comments]').each((index, element) => {
                const $element = $(element);
                const postId = $element.data('post-id') || 0;
                
                VK.Widgets.Comments(element.id, {
                    limit: 10,
                    attach: false,
                    pageUrl: window.location.href
                }, postId);
            });
            
            // Кнопки "Мне нравится"
            $('[data-vk-like]').each((index, element) => {
                VK.Widgets.Like(element.id, {
                    pageUrl: window.location.href,
                    height: 20
                });
            });
        },
        
        // Инициализация OK виджетов
        initOKWidgets: function() {
            if (typeof OK === 'undefined') return;
            
            $('[data-ok-widget]').each((index, element) => {
                const $element = $(element);
                const type = $element.data('widget-type') || 'like';
                
                OK.CONNECT.insertWidget(
                    element,
                    type,
                    '{"st.cmd":"WidgetsShare","st.type":"small","st.orientation":"horizontal"}'
                );
            });
        }
    };
    
    // Инициализация
    $(document).ready(() => {
        RussianServices.init();
        
        // Отслеживание соц. кнопок
        $(document).on('click', '.share-btn', function() {
            const network = $(this).data('network') || 'unknown';
            RussianServices.trackSocialAction(network, 'share');
        });
        
        // Обновление курсов каждые 5 минут
        setInterval(() => {
            RussianServices.loadCurrencyRates();
        }, 300000);
    });
    
})(jQuery);