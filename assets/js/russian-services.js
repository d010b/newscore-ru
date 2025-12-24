/**
 * Russian Services Integration
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Загрузка погоды от Яндекс
    function loadYandexWeather() {
        $('.weather-widget').each(function() {
            var $widget = $(this);
            var city = $widget.data('city');
            
            $.ajax({
                url: 'https://api.weather.yandex.ru/v2/forecast',
                method: 'GET',
                data: {
                    lat: getCityCoords(city).lat,
                    lon: getCityCoords(city).lon,
                    lang: 'ru_RU',
                    limit: 1
                },
                headers: {
                    'X-Yandex-API-Key': 'your-api-key-here' // Нужно получить на https://developer.tech.yandex.ru/
                },
                success: function(response) {
                    if (response.fact) {
                        var html = '<div class="weather-info">';
                        html += '<div class="city">' + city + '</div>';
                        html += '<div class="temperature">' + response.fact.temp + '°C</div>';
                        html += '<div class="condition">' + response.fact.condition + '</div>';
                        html += '<div class="details">';
                        html += '<span class="wind">Ветер: ' + response.fact.wind_speed + ' м/с</span>';
                        html += '<span class="humidity">Влажность: ' + response.fact.humidity + '%</span>';
                        html += '</div>';
                        html += '</div>';
                        
                        $widget.html(html);
                    }
                },
                error: function() {
                    $widget.html('<div class="weather-error">Не удалось загрузить погоду</div>');
                }
            });
        });
    }
    
    // Координаты городов России
    function getCityCoords(city) {
        var cities = {
            'Москва': { lat: 55.7558, lon: 37.6173 },
            'Санкт-Петербург': { lat: 59.9343, lon: 30.3351 },
            'Новосибирск': { lat: 55.0084, lon: 82.9357 },
            'Екатеринбург': { lat: 56.8389, lon: 60.6057 },
            'Казань': { lat: 55.7961, lon: 49.1064 },
            'Нижний Новгород': { lat: 56.3269, lon: 44.025 },
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
    }
    
    // Загрузка курсов валют ЦБ РФ
    function loadExchangeRates() {
        $('.exchange-rates-widget').each(function() {
            var $widget = $(this);
            
            $.ajax({
                url: 'https://www.cbr-xml-daily.ru/daily_json.js',
                method: 'GET',
                success: function(response) {
                    var data = JSON.parse(response);
                    var html = '<div class="exchange-rates">';
                    
                    // USD
                    if (data.Valute.USD) {
                        var usd = data.Valute.USD;
                        var change = usd.Value - usd.Previous;
                        var changeClass = change > 0 ? 'positive' : 'negative';
                        html += '<div class="exchange-rate">';
                        html += '<div class="currency">USD/₽</div>';
                        html += '<div class="rate">' + usd.Value.toFixed(2) + '</div>';
                        html += '<div class="change ' + changeClass + '">' + (change > 0 ? '+' : '') + change.toFixed(2) + '</div>';
                        html += '</div>';
                    }
                    
                    // EUR
                    if (data.Valute.EUR) {
                        var eur = data.Valute.EUR;
                        var change = eur.Value - eur.Previous;
                        var changeClass = change > 0 ? 'positive' : 'negative';
                        html += '<div class="exchange-rate">';
                        html += '<div class="currency">EUR/₽</div>';
                        html += '<div class="rate">' + eur.Value.toFixed(2) + '</div>';
                        html += '<div class="change ' + changeClass + '">' + (change > 0 ? '+' : '') + change.toFixed(2) + '</div>';
                        html += '</div>';
                    }
                    
                    // CNY
                    if (data.Valute.CNY) {
                        var cny = data.Valute.CNY;
                        var change = cny.Value - cny.Previous;
                        var changeClass = change > 0 ? 'positive' : 'negative';
                        html += '<div class="exchange-rate">';
                        html += '<div class="currency">CNY/₽</div>';
                        html += '<div class="rate">' + cny.Value.toFixed(2) + '</div>';
                        html += '<div class="change ' + changeClass + '">' + (change > 0 ? '+' : '') + change.toFixed(2) + '</div>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    $widget.html(html);
                },
                error: function() {
                    $widget.html('<div class="exchange-error">Не удалось загрузить курсы валют</div>');
                }
            });
        });
    }
    
    // Яндекс.Карты
    function initYandexMaps() {
        if (typeof ymaps !== 'undefined') {
            $('[data-yandex-map]').each(function() {
                var $map = $(this);
                var lat = $map.data('lat');
                var lon = $map.data('lon');
                var zoom = $map.data('zoom') || 12;
                
                ymaps.ready(function() {
                    var map = new ymaps.Map($map.attr('id'), {
                        center: [lat, lon],
                        zoom: zoom,
                        controls: ['zoomControl', 'fullscreenControl']
                    });
                    
                    var placemark = new ymaps.Placemark([lat, lon], {
                        balloonContent: $map.data('title') || 'Наш офис'
                    });
                    
                    map.geoObjects.add(placemark);
                });
            });
        }
    }
    
    // Трекер для российских соцсетей
    function trackRussianSocialShares() {
        $('.share-btn').on('click', function(e) {
            var social = $(this).hasClass('vk') ? 'vk' :
                        $(this).hasClass('ok') ? 'ok' :
                        $(this).hasClass('telegram') ? 'telegram' :
                        $(this).hasClass('yandex') ? 'yandex' : 'mailru';
            
            // Отправляем данные в Яндекс.Метрику
            if (typeof ym !== 'undefined') {
                ym(<?php echo get_theme_mod('yandex_metrika_id', 0); ?>, 'reachGoal', 'social_share_' + social);
            }
            
            // Google Analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'social_share', {
                    'event_category': 'social',
                    'event_label': social,
                    'value': 1
                });
            }
        });
    }
    
    // Определение российского праздника
    function getRussianHoliday() {
        var today = new Date();
        var month = today.getMonth() + 1;
        var day = today.getDate();
        
        var holidays = {
            '1-1': 'Новый год',
            '1-2': 'Новогодние каникулы',
            '1-7': 'Рождество Христово',
            '2-23': 'День защитника Отечества',
            '3-8': 'Международный женский день',
            '5-1': 'Праздник Весны и Труда',
            '5-9': 'День Победы',
            '6-12': 'День России',
            '11-4': 'День народного единства'
        };
        
        var key = month + '-' + day;
        if (holidays[key]) {
            $('.holiday-banner').text('Сегодня: ' + holidays[key]);
            $('.holiday-banner').show();
        }
    }
    
    // Инициализация
    if ($('.weather-widget').length) {
        loadYandexWeather();
    }
    
    if ($('.exchange-rates-widget').length) {
        loadExchangeRates();
        // Обновляем каждые 5 минут
        setInterval(loadExchangeRates, 300000);
    }
    
    if ($('[data-yandex-map]').length) {
        initYandexMaps();
    }
    
    if ($('.share-btn').length) {
        trackRussianSocialShares();
    }
    
    if ($('.holiday-banner').length) {
        getRussianHoliday();
    }
    
    // Адаптация для Яндекса.Браузера
    if (navigator.userAgent.indexOf('YaBrowser') !== -1) {
        $('body').addClass('yandex-browser');
        
        // Оптимизация для Турбо-режима
        if (window.outerWidth === 0) {
            // Турбо-режим активен
            $('img').each(function() {
                var src = $(this).attr('src');
                if (src && !src.includes('data:image')) {
                    $(this).attr('data-src', src);
                    $(this).removeAttr('src');
                }
            });
        }
    }
    
    // Определение региона пользователя
    function detectUserRegion() {
        $.ajax({
            url: 'https://api.sypexgeo.net/json/',
            method: 'GET',
            success: function(response) {
                if (response.country && response.country.iso === 'RU') {
                    localStorage.setItem('user_region', response.region.name_en);
                    localStorage.setItem('user_city', response.city.name_en);
                    
                    // Показываем региональный контент
                    $('.regional-content').each(function() {
                        var region = $(this).data('region');
                        if (region === response.region.name_en || region === 'all') {
                            $(this).show();
                        }
                    });
                }
            }
        });
    }
    
    // Проверяем, нужно ли определить регион
    if (!localStorage.getItem('user_region')) {
        detectUserRegion();
    }
});