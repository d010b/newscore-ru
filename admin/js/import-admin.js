/**
 * NewsCore Import Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Импорт отдельной секции
    $('.import-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var section = $button.data('section');
        var $status = $('#status-' + section);
        
        // Блокируем кнопку
        $button.prop('disabled', true).text('Импорт...');
        
        // Показываем статус загрузки
        $status
            .removeClass('success error')
            .addClass('loading')
            .text(newscore_import.importing_text)
            .show();
        
        // Отправляем AJAX запрос
        $.ajax({
            url: newscore_import.ajax_url,
            method: 'POST',
            data: {
                action: 'import_test_content',
                section: section,
                nonce: newscore_import.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status
                        .removeClass('loading')
                        .addClass('success')
                        .html('<strong>' + newscore_import.success_text + '</strong> ' + response.message);
                    
                    // Добавляем ссылки если есть
                    if (response.edit_link) {
                        $status.append(
                            '<div class="status-links">' +
                            '<a href="' + response.edit_link + '" target="_blank">Редактировать</a>' +
                            '<a href="' + response.view_link + '" target="_blank">Просмотреть</a>' +
                            '</div>'
                        );
                    }
                } else {
                    $status
                        .removeClass('loading')
                        .addClass('error')
                        .html('<strong>' + newscore_import.error_text + '</strong> ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                $status
                    .removeClass('loading')
                    .addClass('error')
                    .html('<strong>' + newscore_import.error_text + '</strong> ' + error);
            },
            complete: function() {
                // Разблокируем кнопку через 3 секунды
                setTimeout(function() {
                    $button.prop('disabled', false).text($button.data('original-text') || 'Создать');
                }, 3000);
            }
        });
    });
    
    // Импорт всего контента
    $('.import-all-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Вы уверены? Это действие создаст весь тестовый контент и может перезаписать существующие данные.')) {
            return;
        }
        
        var $button = $(this);
        var $progress = $('.import-progress');
        var $progressFill = $('.progress-fill');
        var $progressText = $('.progress-text');
        var $status = $('#status-all');
        
        var sections = [
            'categories',
            'news_post',
            'widgets',
            'theme_settings',
            'credentials',
            'news_agency'
        ];
        
        var currentSection = 0;
        var totalSections = sections.length;
        
        // Блокируем все кнопки
        $('.import-btn, .import-all-btn').prop('disabled', true);
        
        // Показываем прогресс
        $progress.show();
        $status
            .removeClass('success error')
            .addClass('loading')
            .text('Начинаем импорт...')
            .show();
        
        // Функция для импорта следующей секции
        function importNextSection() {
            if (currentSection >= totalSections) {
                // Все секции импортированы
                completeImport();
                return;
            }
            
            var section = sections[currentSection];
            var progress = Math.round((currentSection / totalSections) * 100);
            
            // Обновляем прогресс
            $progressFill.css('width', progress + '%');
            $progressText.text('Импорт секции ' + (currentSection + 1) + ' из ' + totalSections);
            
            // Импортируем секцию
            $.ajax({
                url: newscore_import.ajax_url,
                method: 'POST',
                data: {
                    action: 'import_test_content',
                    section: section,
                    nonce: newscore_import.nonce
                },
                success: function(response) {
                    if (response.success) {
                        currentSection++;
                        $status.html(
                            '<strong>✓ Секция ' + currentSection + ':</strong> ' + 
                            response.message + '<br>' + 
                            $status.html()
                        );
                        
                        // Импортируем следующую секцию
                        setTimeout(importNextSection, 1000);
                    } else {
                        // Ошибка - останавливаем
                        $status
                            .removeClass('loading')
                            .addClass('error')
                            .html('<strong>Ошибка в секции ' + section + ':</strong> ' + response.message);
                        completeImport(true);
                    }
                },
                error: function(xhr, status, error) {
                    $status
                        .removeClass('loading')
                        .addClass('error')
                        .html('<strong>Ошибка сети:</strong> ' + error);
                    completeImport(true);
                }
            });
        }
        
        // Функция завершения импорта
        function completeImport(isError) {
            $progressFill.css('width', '100%');
            
            if (!isError) {
                // Финальный AJAX запрос для импорта всего
                $.ajax({
                    url: newscore_import.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'import_test_content',
                        section: 'all',
                        nonce: newscore_import.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $status
                                .removeClass('loading')
                                .addClass('success')
                                .html('<strong>Импорт завершен успешно!</strong><br>' + response.message);
                            
                            // Показываем сводку
                            if (response.summary) {
                                var summary = '<div class="section-info"><p><strong>Создано:</strong></p><ul>';
                                for (var key in response.summary) {
                                    summary += '<li>' + response.summary[key] + '</li>';
                                }
                                summary += '</ul></div>';
                                $status.append(summary);
                            }
                            
                            $progressText.text('Импорт завершен!');
                        } else {
                            $status
                                .removeClass('loading')
                                .addClass('error')
                                .html('<strong>Ошибка финального импорта:</strong> ' + response.message);
                        }
                    }
                });
            }
            
            // Разблокируем кнопки через 5 секунд
            setTimeout(function() {
                $('.import-btn, .import-all-btn').prop('disabled', false);
            }, 5000);
        }
        
        // Начинаем импорт
        importNextSection();
    });
    
    // Сохраняем оригинальный текст кнопок
    $('.import-btn').each(function() {
        $(this).data('original-text', $(this).text());
    });
});