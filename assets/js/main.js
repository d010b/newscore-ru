/**
 * NewsCore Main JavaScript
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Мобильное меню
    $('.menu-toggle').click(function() {
        $(this).toggleClass('active');
        $('#primary-menu').slideToggle();
    });
    
    // Поиск
    $('.search-toggle').click(function() {
        $('.search-form-container').toggleClass('active');
        if ($('.search-form-container').hasClass('active')) {
            $('.search-form-container input[type="search"]').focus();
        }
    });
    
    // Закрытие поиска при клике вне его
    $(document).click(function(e) {
        if (!$(e.target).closest('.header-search').length) {
            $('.search-form-container').removeClass('active');
        }
    });
    
    // Бегущая строка (если используется)
    function initTicker() {
        $('.ticker-content').each(function() {
            const $ticker = $(this);
            const $items = $ticker.children();
            const itemCount = $items.length;
            
            if (itemCount > 1) {
                let current = 0;
                
                function showNext() {
                    $items.eq(current).fadeOut(500, function() {
                        current = (current + 1) % itemCount;
                        $items.eq(current).fadeIn(500);
                    });
                }
                
                // Меняем каждые 5 секунд
                setInterval(showNext, 5000);
            }
        });
    }
    initTicker();
    
    // Подгрузка новостей
    $('.load-more-btn').click(function() {
        const $button = $(this);
        const page = parseInt($button.data('page')) + 1;
        const maxPages = parseInt($button.data('max'));
        
        $button.text('Loading...').prop('disabled', true);
        
        $.ajax({
            url: newsCore.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_posts',
                page: page,
                security: newsCore.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.posts-grid').append(response.data.html);
                    $button.data('page', page);
                    
                    if (page >= maxPages) {
                        $button.remove();
                    } else {
                        $button.text('Load More').prop('disabled', false);
                    }
                }
            }
        });
    });
    
    // Обработка просмотров
    if ($('body').hasClass('single-post')) {
        const postId = $('article').attr('id').replace('post-', '');
        
        // Увеличиваем счетчик просмотров
        $.post(newsCore.ajaxurl, {
            action: 'update_post_views',
            post_id: postId,
            security: newsCore.nonce
        });
    }
    
    // Плавная прокрутка
    $('a[href*="#"]').not('[href="#"]').click(function(e) {
        if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') 
            && location.hostname === this.hostname) {
            const target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        }
    });
    
    // Кнопка "Наверх"
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').addClass('visible');
        } else {
            $('.back-to-top').removeClass('visible');
        }
    });
    
    $('.back-to-top').click(function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 500);
    });
});