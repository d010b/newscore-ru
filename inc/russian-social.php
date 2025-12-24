<?php
/**
 * Russian social networks integration
 */

// VK Widgets
function newscore_vk_widgets() {
    // VK Comments
    if (get_theme_mod('vk_comments_app_id') && is_single()) {
        echo '<div id="vk_comments"></div>';
        echo '<script type="text/javascript">
            VK.Widgets.Comments("vk_comments", {
                limit: 10,
                attach: "*",
                autoPublish: 1,
                pageUrl: "' . get_permalink() . '"
            }, "' . get_the_ID() . '");
        </script>';
    }
    
    // VK Like Button
    if (get_theme_mod('vk_like_button')) {
        echo '<div id="vk_like"></div>';
        echo '<script type="text/javascript">
            VK.Widgets.Like("vk_like", {
                type: "button",
                height: 20
            });
        </script>';
    }
}
add_action('wp_footer', 'newscore_vk_widgets');

// VK API Script
function newscore_enqueue_vk_api() {
    if (get_theme_mod('vk_comments_app_id') || get_theme_mod('vk_like_button')) {
        wp_enqueue_script('vk-api', 'https://vk.com/js/api/openapi.js?169', array(), null, true);
        
        $vk_app_id = get_theme_mod('vk_comments_app_id', '');
        echo '<script type="text/javascript">
            VK.init({
                apiId: ' . intval($vk_app_id) . ',
                onlyWidgets: true
            });
        </script>';
    }
}
add_action('wp_enqueue_scripts', 'newscore_enqueue_vk_api');

// OK Widget
function newscore_odnoklassniki_widget() {
    if (get_theme_mod('ok_widget') && is_single()) {
        echo '<div id="ok_group_widget"></div>';
        echo '<script>
            !function (d, id, did, st, title, description) {
                var js = d.createElement("script");
                js.src = "https://connect.ok.ru/connect.js";
                js.onload = js.onreadystatechange = function () {
                    if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
                        if (!this.executed) {
                            this.executed = true;
                            setTimeout(function () {
                                OK.CONNECT.insertGroupWidget(id,did,st,title,description);
                            }, 0);
                        }
                    }
                };
                d.documentElement.appendChild(js);
            }(document,"ok_group_widget","' . esc_js(get_theme_mod('ok_widget_group_id')) . '","' . esc_js(get_theme_mod('ok_widget_style', 'wide')) . '","' . esc_js(get_the_title()) . '","' . esc_js(get_the_excerpt()) . '");
        </script>';
    }
}
add_action('wp_footer', 'newscore_odnoklassniki_widget');

// Telegram Share Button
function newscore_telegram_share_button() {
    if (get_theme_mod('telegram_share') && is_single()) {
        $url = urlencode(get_permalink());
        $text = urlencode(get_the_title());
        
        echo '<div class="telegram-share">
            <a href="https://t.me/share/url?url=' . $url . '&text=' . $text . '" 
               target="_blank" 
               rel="nofollow noopener"
               class="telegram-share-btn">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.06-.2-.07-.06-.17-.04-.24-.03-.1.02-1.79 1.14-5.06 3.35-.48.33-.91.49-1.3.48-.43-.01-1.25-.24-1.86-.44-.75-.24-1.35-.37-1.29-.78.03-.2.32-.41.89-.62 3.5-1.52 5.83-2.52 6.99-3.01 3.22-1.36 3.88-1.6 4.32-1.61.09 0 .29.02.42.12.1.08.13.19.14.27-.01.06.01.28 0 0z"/>
                </svg>
                Поделиться в Telegram
            </a>
        </div>';
    }
}
add_action('newscore_after_post_content', 'newscore_telegram_share_button');

// Rutube Embed
function newscore_rutube_embed_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
        'width' => '640',
        'height' => '360'
    ), $atts);
    
    if (empty($atts['id'])) {
        return '';
    }
    
    return '<div class="rutube-embed">
        <iframe src="https://rutube.ru/play/embed/' . esc_attr($atts['id']) . '" 
                width="' . esc_attr($atts['width']) . '" 
                height="' . esc_attr($atts['height']) . '" 
                frameborder="0" 
                allow="autoplay; encrypted-media" 
                allowfullscreen>
        </iframe>
    </div>';
}
add_shortcode('rutube', 'newscore_rutube_embed_shortcode');

// Dzen Embed
function newscore_yandex_dzen_embed($content) {
    if (get_theme_mod('enable_dzen_embed') && is_single()) {
        $dzen_widget = '<div class="yandex-dzen-widget">
            <script src="https://zen.yandex.ru/widget/loader?from=partner"></script>
            <div class="zen-widget" data-widget="recommend">
                <a href="https://zen.yandex.ru" target="_blank" rel="noopener">
                    <img src="https://yastatic.net/s3/home/zen/logo-ru.svg" alt="Яндекс.Дзен" width="100" height="30">
                </a>
            </div>
        </div>';
        
        $content .= $dzen_widget;
    }
    
    return $content;
}
add_filter('the_content', 'newscore_yandex_dzen_embed');

// Russian Share Buttons
function newscore_russian_share_buttons() {
    if (!is_single()) return;
    
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    $image = has_post_thumbnail() ? urlencode(get_the_post_thumbnail_url(null, 'full')) : '';
    ?>
    <div class="russian-share-buttons">
        <h4>Поделиться:</h4>
        <div class="share-buttons-grid">
            <!-- VK -->
            <a href="https://vk.com/share.php?url=<?php echo $url; ?>&title=<?php echo $title; ?>&image=<?php echo $image; ?>" 
               target="_blank" 
               class="share-btn vk"
               title="Поделиться ВКонтакте">
                <i class="fa fa-vk"></i> ВК
            </a>
            
            <!-- Одноклассники -->
            <a href="https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl=<?php echo $url; ?>" 
               target="_blank" 
               class="share-btn ok"
               title="Поделиться в Одноклассниках">
                <i class="fa fa-odnoklassniki"></i> ОК
            </a>
            
            <!-- Telegram -->
            <a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" 
               target="_blank" 
               class="share-btn telegram"
               title="Поделиться в Telegram">
                <i class="fa fa-telegram"></i> TG
            </a>
            
            <!-- Яндекс -->
            <a href="https://news.yandex.ru/yandbook?issue_id=<?php echo urlencode(get_permalink()); ?>" 
               target="_blank" 
               class="share-btn yandex"
               title="Добавить в Яндекс.Новости">
                <i class="fa fa-yahoo"></i> Яндекс
            </a>
            
            <!-- Mail.ru -->
            <a href="https://connect.mail.ru/share?url=<?php echo $url; ?>&title=<?php echo $title; ?>" 
               target="_blank" 
               class="share-btn mailru"
               title="Поделиться в Mail.ru">
                <i class="fa fa-at"></i> Mail
            </a>
        </div>
    </div>
    <?php
}
add_action('newscore_after_post_content', 'newscore_russian_share_buttons', 20);