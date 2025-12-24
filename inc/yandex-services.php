<?php
/**
 * Yandex services integration
 */

// Яндекс.Новости RSS
function newscore_yandex_news_rss() {
    if (get_theme_mod('enable_yandex_news')) {
        add_feed('yandex-news', 'newscore_yandex_news_feed');
    }
}
add_action('init', 'newscore_yandex_news_rss');

function newscore_yandex_news_feed() {
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    
    echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?>';
?>
<rss version="2.0" 
     xmlns:yandex="http://news.yandex.ru"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:turbo="http://turbo.yandex.ru">
    <channel>
        <title><?php bloginfo_rss('name'); ?></title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <language>ru</language>
        <?php if (get_theme_mod('yandex_news_category')) : ?>
        <category><?php echo esc_html(get_theme_mod('yandex_news_category')); ?></category>
        <?php endif; ?>
        
        <?php
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 50,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'date_query' => array(
                array(
                    'after' => '24 hours ago'
                )
            )
        );
        
        $query = new WP_Query($args);
        
        while ($query->have_posts()) : $query->the_post();
            $content = get_the_content_feed('rss2');
            $content = strip_tags($content, '<p><br><h1><h2><h3><h4><h5><h6><ul><ol><li><img><figure><figcaption><blockquote><table><tr><td><th>');
        ?>
        <item turbo="true">
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <author><?php the_author(); ?></author>
            <yandex:full-text><?php echo '<![CDATA[' . $content . ']]>'; ?></yandex:full-text>
            <?php if (has_post_thumbnail()) : ?>
            <enclosure url="<?php echo esc_url(get_the_post_thumbnail_url(null, 'full')); ?>" type="image/jpeg" />
            <?php endif; ?>
            <category><?php 
                $categories = get_the_category();
                if (!empty($categories)) {
                    echo esc_html($categories[0]->name);
                }
            ?></category>
        </item>
        <?php endwhile; wp_reset_postdata(); ?>
    </channel>
</rss>
<?php
}

// Яндекс.Турбо страницы
function newscore_yandex_turbo_content($content) {
    if (is_single() && get_theme_mod('enable_yandex_turbo')) {
        $turbo_content = $content;
        
        // Удаляем недопустимые теги для Турбо
        $turbo_content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $turbo_content);
        $turbo_content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $turbo_content);
        $turbo_content = preg_replace('/<form\b[^>]*>(.*?)<\/form>/is', '', $turbo_content);
        
        // Добавляем структуру Турбо
        $turbo_content = '<header>' . get_the_title() . '</header>' . $turbo_content;
        
        return $turbo_content;
    }
    return $content;
}
add_filter('the_content', 'newscore_yandex_turbo_content');

// Яндекс.Дзен RSS
function newscore_yandex_zen_rss() {
    if (get_theme_mod('enable_yandex_zen')) {
        add_feed('yandex-zen', 'newscore_yandex_zen_feed');
    }
}
add_action('init', 'newscore_yandex_zen_rss');

function newscore_yandex_zen_feed() {
    header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
    
    echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><?php bloginfo_rss('name'); ?></title>
        <link><?php bloginfo_rss('url'); ?></link>
        <description><?php bloginfo_rss('description'); ?></description>
        <language>ru</language>
        <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
        
        <?php
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 20,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        
        while ($query->have_posts()) : $query->the_post();
            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'full') : '';
        ?>
        <item>
            <title><?php the_title_rss(); ?></title>
            <link><?php the_permalink_rss(); ?></link>
            <guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
            <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
            <author><?php the_author(); ?></author>
            <?php if ($image_url) : ?>
            <enclosure url="<?php echo esc_url($image_url); ?>" type="image/jpeg" />
            <?php endif; ?>
            <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
            <content:encoded><![CDATA[<?php 
                $content = get_the_content_feed('rss2');
                echo wpautop($content);
            ?>]]></content:encoded>
            <category><?php 
                $categories = get_the_category();
                if (!empty($categories)) {
                    echo esc_html($categories[0]->name);
                }
            ?></category>
        </item>
        <?php endwhile; wp_reset_postdata(); ?>
    </channel>
</rss>
<?php
}

// Яндекс.Поиск для сайта
function newscore_yandex_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => '40px'
    ), $atts);
    
    return '<div class="yandex-search-widget">
        <form action="https://yandex.ru/search/site/" method="get" target="_blank">
            <input type="hidden" name="searchid" value="2277271" />
            <input type="hidden" name="l10n" value="ru" />
            <input type="hidden" name="reqenc" value="" />
            <input type="search" name="text" placeholder="Поиск по сайту через Яндекс" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . '" />
            <button type="submit">Найти</button>
        </form>
    </div>';
}
add_shortcode('yandex_search', 'newscore_yandex_search_shortcode');

// Яндекс.Карты
function newscore_yandex_map_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => '400px',
        'lat' => '55.751244',
        'lon' => '37.618423',
        'zoom' => '10'
    ), $atts);
    
    $map_id = 'yandex-map-' . uniqid();
    
    $output = '<div id="' . esc_attr($map_id) . '" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . ';"></div>';
    $output .= '<script>
        ymaps.ready(function() {
            var map = new ymaps.Map("' . $map_id . '", {
                center: [' . floatval($atts['lat']) . ', ' . floatval($atts['lon']) . '],
                zoom: ' . intval($atts['zoom']) . '
            });
        });
    </script>';
    
    return $output;
}
add_shortcode('yandex_map', 'newscore_yandex_map_shortcode');

// Загрузка API Яндекс.Карт
function newscore_enqueue_yandex_maps() {
    if (is_single() || is_page()) {
        global $post;
        if (has_shortcode($post->post_content, 'yandex_map')) {
            wp_enqueue_script('yandex-maps', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), null, true);
        }
    }
}
add_action('wp_enqueue_scripts', 'newscore_enqueue_yandex_maps');

// Яндекс.Деньги для донатов
function newscore_yandex_money_donate($atts) {
    $atts = shortcode_atts(array(
        'account' => '',
        'text' => 'Поддержать проект',
        'color' => 'blue'
    ), $atts);
    
    if (empty($atts['account'])) {
        return '';
    }
    
    return '<div class="yandex-donate">
        <iframe src="https://money.yandex.ru/quickpay/shop-widget?writer=seller&targets=' . urlencode($atts['text']) . '&targets-hint=&default-sum=100&button-text=11&payment-type-choice=on&mobile-payment-type-choice=on&hint=&successURL=&quickpay=shop&account=' . urlencode($atts['account']) . '" width="450" height="230" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
    </div>';
}
add_shortcode('yandex_donate', 'newscore_yandex_money_donate');