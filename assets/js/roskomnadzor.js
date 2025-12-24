/**
 * Roskomnadzor Requirements JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Cookie Notice
    function initCookieNotice() {
        var $cookieNotice = $('#roskom-cookie-notice');
        
        if ($cookieNotice.length) {
            // Принятие cookies
            $('.cookie-accept').on('click', function() {
                setCookie('newscore_cookie_accepted', 'true', 365);
                $cookieNotice.fadeOut(300);
                
                // Отправляем событие в Яндекс.Метрику
                if (typeof ym !== 'undefined') {
                    ym(newscore_roskom.yandex_metrika_id, 'reachGoal', 'cookie_accepted');
                }
                
                // Google Analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'cookie_accept', {
                        'event_category': 'cookie',
                        'event_label': 'accepted'
                    });
                }
            });
            
            // Отклонение cookies
            $('.cookie-reject').on('click', function() {
                setCookie('newscore_cookie_accepted', 'false', 365);
                $cookieNotice.fadeOut(300);
                
                // Отключаем аналитику
                disableAnalytics();
                
                // Отправляем событие
                if (typeof ym !== 'undefined') {
                    ym(newscore_roskom.yandex_metrika_id, 'reachGoal', 'cookie_rejected');
                }
            });
        }
    }
    
    // Age Restriction Gate
    function initAgeGate() {
        var $ageGate = $('#roskom-age-gate');
        
        if ($ageGate.length) {
            // Подтверждение возраста
            $('.age-confirm').on('click', function() {
                var day = $('#age-day').val();
                var month = $('#age-month').val();
                var year = $('#age-year').val();
                
                if (!day || !month || !year) {
                    alert('Пожалуйста, выберите полную дату рождения');
                    return;
                }
                
                var birthDate = new Date(year, month - 1, day);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                
                // Проверяем день рождения в этом году
                if (today.getMonth() < birthDate.getMonth() || 
                    (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                if (age >= 18) {
                    setCookie('newscore_age_confirmed', 'true', 1); // На 1 день
                    $ageGate.fadeOut(300);
                    
                    // Записываем согласие
                    $.ajax({
                        url: newscore_roskom.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'record_age_consent',
                            age: age,
                            birth_date: year + '-' + month + '-' + day,
                            nonce: newscore_roskom.nonce
                        }
                    });
                } else {
                    alert('Доступ запрещен. Вам меньше 18 лет.');
                }
            });
            
            // Выход с сайта
            $('.age-exit').on('click', function() {
                window.location.href = 'https://www.google.com';
            });
            
            // Автоматический скроллинг только внутри age gate
            $ageGate.on('wheel touchmove', function(e) {
                e.preventDefault();
            });
            
            // Блокируем клавиатуру
            $(document).on('keydown', function(e) {
                if ($ageGate.is(':visible')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
    
    // Валидация форм с согласием
    function initConsentForms() {
        $('form').each(function() {
            var $form = $(this);
            
            if ($form.find('input[name="personal_data_consent"]').length) {
                $form.on('submit', function(e) {
                    var $consentCheckbox = $form.find('input[name="personal_data_consent"]');
                    
                    if (!$consentCheckbox.is(':checked')) {
                        e.preventDefault();
                        alert('Необходимо согласиться на обработку персональных данных');
                        $consentCheckbox.focus();
                        return false;
                    }
                    
                    // Записываем согласие
                    var formData = $form.serialize();
                    $.ajax({
                        url: newscore_roskom.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'record_personal_data_consent',
                            form_data: formData,
                            nonce: newscore_roskom.nonce
                        }
                    });
                });
            }
        });
    }
    
    // GDPR Экспорт данных
    function initGDPRExport() {
        $('.gdpr-export-btn').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('Вы уверены, что хотите экспортировать все ваши персональные данные?')) {
                $.ajax({
                    url: newscore_roskom.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'export_personal_data',
                        nonce: newscore_roskom.nonce
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob) {
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'personal-data-export-' + new Date().toISOString().split('T')[0] + '.json';
                        link.click();
                    }
                });
            }
        });
    }
    
    // Управление возрастными ограничениями
    function initAgeRestrictions() {
        // Для постов с возрастными ограничениями
        $('.post-age-badge').each(function() {
            var $badge = $(this);
            var ageRating = $badge.attr('class').match(/age-(\d+\+)/);
            
            if (ageRating) {
                var minAge = parseInt(ageRating[1]);
                var userAge = getCookie('user_age');
                
                if (userAge && userAge < minAge) {
                    // Показываем предупреждение
                    showAgeWarning(minAge);
                }
            }
        });
    }
    
    // Вспомогательные функции
    function setCookie(name, value, days) {
        var expires = '';
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + (value || '') + expires + '; path=/; Secure; SameSite=Lax';
    }
    
    function getCookie(name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    
    function disableAnalytics() {
        // Отключаем Яндекс.Метрику
        if (typeof ym !== 'undefined') {
            // Создаем скрипт для отключения
            var script = document.createElement('script');
            script.textContent = 'window["yaCounter" + ' + newscore_roskom.yandex_metrika_id + '] = {reachGoal:function(){},hit:function(){}};';
            document.head.appendChild(script);
        }
        
        // Отключаем Google Analytics
        if (typeof ga !== 'undefined') {
            window['ga-disable-' + newscore_roskom.google_analytics_id] = true;
        }
        
        // Удаляем cookies аналитики
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf('=');
            var name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
            
            if (name.includes('_ga') || name.includes('_ym')) {
                document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
            }
        }
    }
    
    function showAgeWarning(minAge) {
        var warning = '<div class="age-access-warning">' +
            '<h4>Доступ ограничен</h4>' +
            '<p>Этот материал предназначен для лиц старше ' + minAge + ' лет.</p>' +
            '<button class="btn-back">Вернуться назад</button>' +
            '</div>';
        
        $('body').append(warning);
        
        $('.btn-back').on('click', function() {
            window.history.back();
        });
        
        // Блокируем контент
        $('article').css('filter', 'blur(5px)');
        $('article').css('pointer-events', 'none');
    }
    
    // Автоматическое обновление политики конфиденциальности
    function checkPrivacyPolicyUpdate() {
        var lastAccepted = getCookie('privacy_policy_accepted');
        var currentVersion = newscore_roskom.privacy_version;
        
        if (lastAccepted && lastAccepted !== currentVersion) {
            showPrivacyUpdateModal();
        }
    }
    
    function showPrivacyUpdateModal() {
        var modal = '<div id="privacy-update-modal" class="roskom-modal">' +
            '<div class="modal-content">' +
            '<h3>Обновление политики конфиденциальности</h3>' +
            '<p>Мы обновили нашу политику конфиденциальности. Пожалуйста, ознакомьтесь с изменениями.</p>' +
            '<div class="modal-actions">' +
            '<button class="modal-btn view-changes">Посмотреть изменения</button>' +
            '<button class="modal-btn accept-new">Принять изменения</button>' +
            '</div>' +
            '</div>' +
            '</div>';
        
        $('body').append(modal);
        
        $('.view-changes').on('click', function() {
            window.open(newscore_roskom.privacy_page_url, '_blank');
        });
        
        $('.accept-new').on('click', function() {
            setCookie('privacy_policy_accepted', newscore_roskom.privacy_version, 365);
            $('#privacy-update-modal').remove();
        });
    }
    
    // Запись согласий
    function recordConsent(type, data) {
        $.ajax({
            url: newscore_roskom.ajax_url,
            method: 'POST',
            data: {
                action: 'record_consent',
                consent_type: type,
                consent_data: JSON.stringify(data),
                nonce: newscore_roskom.nonce
            }
        });
    }
    
    // Инициализация
    initCookieNotice();
    initAgeGate();
    initConsentForms();
    initGDPRExport();
    initAgeRestrictions();
    checkPrivacyPolicyUpdate();
    
    // Глобальные обработчики
    $(document).on('click', '.age-badge-info', function(e) {
        e.preventDefault();
        var age = $(this).data('age');
        alert('Этот материал содержит информацию, не рекомендованную для лиц младше ' + age + ' лет в соответствии с ФЗ-436 "О защите детей от информации".');
    });
    
    // Экспорт данных по запросу
    $(document).on('submit', '.gdpr-data-request', function(e) {
        e.preventDefault();
        var email = $(this).find('input[type="email"]').val();
        
        if (email) {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'request_data_export',
                    email: email,
                    nonce: newscore_roskom.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Запрос на экспорт данных отправлен. Проверьте вашу почту.');
                    }
                }
            });
        }
    });
    
    // Отслеживание отзывов согласия
    $(document).on('click', '.withdraw-consent', function() {
        if (confirm('Вы уверены, что хотите отозвать согласие на обработку персональных данных? Это может ограничить функциональность сайта.')) {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'withdraw_consent',
                    consent_type: $(this).data('type'),
                    nonce: newscore_roskom.nonce
                },
                success: function() {
                    alert('Согласие отозвано.');
                    location.reload();
                }
            });
        }
    });
});