/**
 * Roskomnadzor Requirements JavaScript - Secure Version 2.0.1
 * Безопасная реализация требований Роскомнадзора
 */

(function($) {
    'use strict';
    
    // Проверка наличия объекта настроек
    if (typeof newscore_roskom === 'undefined') {
        console.warn('Конфигурация Роскомнадзора не загружена');
        return;
    }
    
    // Основной объект
    const Roskomnadzor = {
        
        // Инициализация
        init: function() {
            this.initCookieNotice();
            this.initAgeGate();
            this.initConsentForms();
            this.initGDPR();
            this.bindEvents();
        },
        
        // Cookie уведомление
        initCookieNotice: function() {
            const $notice = $('#roskom-cookie-notice');
            
            if (!$notice.length) return;
            
            // Проверяем решение пользователя
            const decision = this.getCookie('newscore_cookie_decision');
            if (decision) {
                if (decision === 'rejected') {
                    this.disableAnalytics();
                }
                $notice.remove();
                return;
            }
            
            // Показываем с задержкой
            setTimeout(() => {
                $notice
                    .fadeIn(300)
                    .attr('aria-hidden', 'false')
                    .removeAttr('style');
                
                // Фокус на первой кнопке
                $notice.find('.cookie-accept').first().focus();
            }, 1000);
            
            // Закрытие при клике вне уведомления
            $(document).on('click', (e) => {
                if (!$notice.is(e.target) && $notice.has(e.target).length === 0) {
                    this.hideCookieNotice();
                }
            });
        },
        
        // Возрастной гейт
        initAgeGate: function() {
            const $gate = $('#roskom-age-gate');
            
            if (!$gate.length || !newscore_roskom.age_restriction) return;
            
            // Проверяем подтверждение
            if (this.getCookie('newscore_age_confirmed') === 'true') {
                $gate.remove();
                return;
            }
            
            // Показываем гейт
            $gate.show().attr('aria-hidden', 'false');
            $('body').addClass('age-gate-active');
            
            // Фокус
            setTimeout(() => {
                $gate.find('#age-day').focus();
            }, 400);
        },
        
        // Формы с согласием
        initConsentForms: function() {
            $('form[data-requires-consent]').each((i, form) => {
                const $form = $(form);
                const $checkbox = $form.find('input[name="personal_data_consent"]');
                
                if (!$checkbox.length) return;
                
                $form.on('submit', (e) => {
                    if (!$checkbox.is(':checked')) {
                        e.preventDefault();
                        this.showFormError($checkbox, 'Необходимо согласие на обработку данных');
                        return false;
                    }
                    
                    // Запись согласия
                    this.recordFormConsent($form.serializeArray());
                });
            });
        },
        
        // GDPR функции
        initGDPR: function() {
            $('.gdpr-export-btn').on('click', (e) => {
                e.preventDefault();
                
                if (confirm('Экспортировать ваши персональные данные в формате JSON?')) {
                    this.exportPersonalData();
                }
            });
            
            $('.gdpr-delete-btn').on('click', (e) => {
                e.preventDefault();
                
                if (confirm('Вы уверены? Это действие нельзя отменить.')) {
                    this.deletePersonalData();
                }
            });
        },
        
        // События
        bindEvents: function() {
            // Cookie кнопки
            $(document)
                .on('click', '.cookie-accept', (e) => this.acceptCookies(e))
                .on('click', '.cookie-reject', (e) => this.rejectCookies(e))
                .on('click', '.cookie-settings', (e) => this.showCookieSettings(e));
            
            // Возрастной гейт
            $(document)
                .on('submit', '#age-gate-form', (e) => this.submitAgeForm(e))
                .on('click', '.age-exit', (e) => this.exitSite(e));
            
            // GDPR
            $(document)
                .on('click', '.withdraw-consent', (e) => this.withdrawConsent(e));
        },
        
        // Обработчики
        acceptCookies: function(e) {
            e.preventDefault();
            
            // Устанавливаем cookies
            this.setCookie('newscore_cookie_decision', 'accepted', 365);
            this.setCookie('newscore_cookie_accepted', 'true', 365);
            
            // Скрываем уведомление
            this.hideCookieNotice();
            
            // Записываем согласие
            this.recordCookieConsent('accepted');
            
            // Уведомление
            this.showNotification('Настройки cookies сохранены', 'success');
        },
        
        rejectCookies: function(e) {
            e.preventDefault();
            
            // Устанавливаем cookies
            this.setCookie('newscore_cookie_decision', 'rejected', 365);
            this.setCookie('newscore_cookie_accepted', 'false', 365);
            
            // Отключаем аналитику
            this.disableAnalytics();
            
            // Скрываем уведомление
            this.hideCookieNotice();
            
            // Записываем отказ
            this.recordCookieConsent('rejected');
            
            // Уведомление
            this.showNotification('Cookies отключены. Некоторые функции могут быть недоступны.', 'info');
        },
        
        submitAgeForm: function(e) {
            e.preventDefault();
            
            const $form = $(e.target);
            const day = $form.find('#age-day').val();
            const month = $form.find('#age-month').val();
            const year = $form.find('#age-year').val();
            
            // Валидация
            if (!this.validateDate(day, month, year)) {
                this.showAgeError('Пожалуйста, введите корректную дату рождения');
                return;
            }
            
            const age = this.calculateAge(day, month, year);
            
            if (age >= 18) {
                // Подтверждаем возраст
                this.setCookie('newscore_age_confirmed', 'true', 30);
                this.setCookie('user_age', age, 30);
                
                // Скрываем гейт
                this.hideAgeGate();
                
                // Записываем согласие
                this.recordAgeConsent(age, year, month, day);
                
                // Уведомление
                this.showNotification('Возраст подтверждён', 'success');
            } else {
                // Показываем ограниченный доступ
                this.showAgeRestrictedModal(age);
            }
        },
        
        // Вспомогательные методы
        getCookie: function(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i].trim();
                if (c.indexOf(nameEQ) === 0) {
                    return decodeURIComponent(c.substring(nameEQ.length));
                }
            }
            return null;
        },
        
        setCookie: function(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            
            let cookieString = [
                name + '=' + encodeURIComponent(value),
                'expires=' + expires.toUTCString(),
                'path=/',
                'SameSite=Lax'
            ].join('; ');
            
            // Secure флаг для HTTPS
            if (window.location.protocol === 'https:') {
                cookieString += '; Secure';
            }
            
            document.cookie = cookieString;
        },
        
        validateDate: function(day, month, year) {
            const d = parseInt(day, 10);
            const m = parseInt(month, 10);
            const y = parseInt(year, 10);
            
            if (isNaN(d) || isNaN(m) || isNaN(y)) return false;
            if (d < 1 || d > 31) return false;
            if (m < 1 || m > 12) return false;
            if (y < 1900 || y > new Date().getFullYear()) return false;
            
            const date = new Date(y, m - 1, d);
            return date.getDate() === d && 
                   date.getMonth() === m - 1 && 
                   date.getFullYear() === y;
        },
        
        calculateAge: function(day, month, year) {
            const birthDate = new Date(year, month - 1, day);
            const today = new Date();
            
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age;
        },
        
        disableAnalytics: function() {
            // Яндекс.Метрика
            if (typeof ym !== 'undefined') {
                window['disableYandexMetrika'] = true;
            }
            
            // Google Analytics
            if (typeof gtag !== 'undefined') {
                window['ga-disable-UA-XXXXX-Y'] = true;
            }
            
            // Удаляем cookies аналитики
            this.removeAnalyticsCookies();
        },
        
        removeAnalyticsCookies: function() {
            const cookies = document.cookie.split(';');
            
            cookies.forEach(cookie => {
                const eqPos = cookie.indexOf('=');
                const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                
                // Паттерны cookies аналитики
                if (name.match(/^(_ga|_gid|_ym|yandexuid)/i)) {
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                }
            });
        },
        
        // UI методы
        hideCookieNotice: function() {
            $('#roskom-cookie-notice').fadeOut(300, function() {
                $(this).attr('aria-hidden', 'true').remove();
            });
        },
        
        hideAgeGate: function() {
            $('#roskom-age-gate').fadeOut(300, function() {
                $(this).attr('aria-hidden', 'true').remove();
                $('body').removeClass('age-gate-active');
            });
        },
        
        showAgeError: function(message) {
            const $error = $('.age-error');
            $error.text(message).fadeIn();
            
            setTimeout(() => {
                $error.fadeOut();
            }, 5000);
        },
        
        showFormError: function($element, message) {
            const $error = $('<div class="form-error" style="color: #dc3545; margin-top: 5px;">' + message + '</div>');
            
            $element.after($error);
            $element.focus();
            
            setTimeout(() => {
                $error.fadeOut(300, () => $error.remove());
            }, 5000);
        },
        
        showNotification: function(message, type = 'info') {
            const $notification = $(`
                <div class="roskom-notification notification-${type}" role="alert" aria-live="polite">
                    <div class="notification-content">${message}</div>
                    <button class="notification-close" aria-label="Закрыть">&times;</button>
                </div>
            `);
            
            $('body').append($notification);
            
            // Анимация
            setTimeout(() => {
                $notification.addClass('show');
            }, 10);
            
            // Закрытие
            $notification.find('.notification-close').on('click', () => {
                $notification.removeClass('show');
                setTimeout(() => $notification.remove(), 300);
            });
            
            // Автозакрытие
            setTimeout(() => {
                if ($notification.hasClass('show')) {
                    $notification.removeClass('show');
                    setTimeout(() => $notification.remove(), 300);
                }
            }, 5000);
        },
        
        // API методы
        recordCookieConsent: function(decision) {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'record_cookie_consent',
                    decision: decision,
                    nonce: newscore_roskom.nonce
                }
            }).fail(() => {
                console.warn('Не удалось записать согласие на cookies');
            });
        },
        
        recordAgeConsent: function(age, year, month, day) {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'record_age_consent',
                    age: age,
                    birth_date: `${year}-${month}-${day}`,
                    nonce: newscore_roskom.nonce
                }
            }).fail(() => {
                console.warn('Не удалось записать согласие на возраст');
            });
        },
        
        recordFormConsent: function(formData) {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'record_personal_data_consent',
                    form_data: formData,
                    nonce: newscore_roskom.nonce
                }
            }).fail(() => {
                console.warn('Не удалось записать согласие на обработку ПД');
            });
        },
        
        exportPersonalData: function() {
            const $button = $('.gdpr-export-btn');
            const originalText = $button.text();
            
            $button.text('Подготовка...').prop('disabled', true);
            
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'export_personal_data',
                    nonce: newscore_roskom.nonce
                },
                xhrFields: {
                    responseType: 'blob'
                }
            })
            .done((blob, status, xhr) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'personal-data-export.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                
                this.showNotification('Данные успешно экспортированы', 'success');
            })
            .fail(() => {
                this.showNotification('Ошибка при экспорте данных', 'error');
            })
            .always(() => {
                $button.text(originalText).prop('disabled', false);
            });
        },
        
        deletePersonalData: function() {
            $.ajax({
                url: newscore_roskom.ajax_url,
                method: 'POST',
                data: {
                    action: 'delete_personal_data',
                    nonce: newscore_roskom.nonce
                }
            })
            .done((response) => {
                if (response.success) {
                    this.showNotification('Данные успешно удалены', 'success');
                    // Перенаправляем на главную
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    this.showNotification(response.data.message, 'error');
                }
            })
            .fail(() => {
                this.showNotification('Ошибка при удалении данных', 'error');
            });
        },
        
        showAgeRestrictedModal: function(age) {
            const modalHtml = `
                <div class="age-restricted-modal" role="dialog" aria-modal="true" aria-labelledby="restricted-title">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <h3 id="restricted-title">Доступ ограничен</h3>
                        <p>Вам ${age} лет. Для доступа к сайту необходимо быть старше 18 лет.</p>
                        <div class="modal-actions">
                            <button class="modal-btn exit-btn">Покинуть сайт</button>
                            <button class="modal-btn back-btn" autofocus>Вернуться</button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            
            // Обработчики
            $('.exit-btn').on('click', () => {
                window.open('https://www.google.com', '_blank');
            });
            
            $('.back-btn').on('click', () => {
                $('.age-restricted-modal').remove();
                $('#age-day').focus();
            });
            
            // Закрытие по ESC
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    $('.age-restricted-modal').remove();
                    $('#age-day').focus();
                }
            });
        },
        
        showCookieSettings: function(e) {
            e.preventDefault();
            
            const settingsHtml = `
                <div class="cookie-settings-modal" role="dialog" aria-modal="true">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <h3>Настройки Cookies</h3>
                        <div class="cookie-options">
                            <div class="cookie-option">
                                <input type="checkbox" id="cookie-essential" checked disabled>
                                <label for="cookie-essential">Необходимые cookies</label>
                                <p class="option-desc">Требуются для работы сайта</p>
                            </div>
                            <div class="cookie-option">
                                <input type="checkbox" id="cookie-analytics" checked>
                                <label for="cookie-analytics">Аналитические cookies</label>
                                <p class="option-desc">Помогают улучшить сайт</p>
                            </div>
                            <div class="cookie-option">
                                <input type="checkbox" id="cookie-marketing">
                                <label for="cookie-marketing">Маркетинговые cookies</label>
                                <p class="option-desc">Для показа релевантной рекламы</p>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="modal-btn save-settings">Сохранить настройки</button>
                            <button class="modal-btn cancel-settings">Отмена</button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(settingsHtml);
            
            // Обработчики
            $('.save-settings').on('click', () => {
                const analytics = $('#cookie-analytics').is(':checked');
                const marketing = $('#cookie-marketing').is(':checked');
                
                this.saveCookieSettings(analytics, marketing);
                $('.cookie-settings-modal').remove();
            });
            
            $('.cancel-settings').on('click', () => {
                $('.cookie-settings-modal').remove();
            });
        },
        
        saveCookieSettings: function(analytics, marketing) {
            const settings = {
                analytics: analytics,
                marketing: marketing,
                timestamp: new Date().toISOString()
            };
            
            this.setCookie('cookie_settings', JSON.stringify(settings), 365);
            
            if (!analytics) {
                this.disableAnalytics();
            }
            
            this.showNotification('Настройки cookies сохранены', 'success');
        },
        
        withdrawConsent: function(e) {
            e.preventDefault();
            
            if (confirm('Отозвать все согласия на обработку данных?')) {
                $.ajax({
                    url: newscore_roskom.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'withdraw_all_consents',
                        nonce: newscore_roskom.nonce
                    }
                })
                .done((response) => {
                    if (response.success) {
                        // Удаляем все наши cookies
                        this.deleteAllConsentCookies();
                        this.showNotification('Все согласия отозваны', 'success');
                    }
                })
                .fail(() => {
                    this.showNotification('Ошибка при отзыве согласий', 'error');
                });
            }
        },
        
        deleteAllConsentCookies: function() {
            const cookies = [
                'newscore_cookie_decision',
                'newscore_cookie_accepted',
                'newscore_age_confirmed',
                'user_age',
                'cookie_settings'
            ];
            
            cookies.forEach(cookie => {
                document.cookie = cookie + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
            });
        },
        
        exitSite: function(e) {
            e.preventDefault();
            window.open('https://www.google.com', '_blank');
        }
    };
    
    // Инициализация при загрузке DOM
    $(document).ready(() => {
        Roskomnadzor.init();
    });
    
})(jQuery);