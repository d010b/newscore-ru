<?php
/**
 * News slider template with multiple display options
 */

// Получаем настройки слайдера из кастомайзера
$slider_type = get_theme_mod('slider_type', 'featured'); // featured, recent, category
$slider_posts_count = get_theme_mod('slider_posts_count', 5);
$slider_category = get_theme_mod('slider_category', 0);
$slider_tag = get_theme_mod('slider_tag', '');
$slider_autoplay = get_theme_mod('slider_autoplay', true);
$slider_autoplay_speed = get_theme_mod('slider_autoplay_speed', 5000);
$slider_animation = get_theme_mod('slider_animation', 'fade');

// Подготавливаем аргументы для WP_Query
$slider_args = array(
    'posts_per_page'      => $slider_posts_count,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => true, // Для производительности
    'meta_query'          => array(
        'relation' => 'OR',
        array(
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS'
        )
    )
);

// Выбор типа слайдера
switch ($slider_type) {
    case 'featured':
        // Посты с меткой "featured"
        $slider_args['tag'] = 'featured';
        break;
        
    case 'sticky':
        // Стики посты
        $slider_args['post__in'] = get_option('sticky_posts');
        break;
        
    case 'category':
        // Посты из выбранной категории
        if ($slider_category) {
            $slider_args['cat'] = $slider_category;
        }
        break;
        
    case 'custom':
        // Кастомное поле (обратная совместимость)
        $slider_args['meta_query'] = array(
            array(
                'key'     => '_slider_post',
                'value'   => '1',
                'compare' => '='
            ),
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS'
            )
        );
        break;
        
    default:
        // Последние посты (по умолчанию)
        $slider_args['orderby'] = 'date';
        $slider_args['order'] = 'DESC';
        break;
}

// Добавляем тег если указан
if (!empty($slider_tag) && $slider_type !== 'featured') {
    $slider_args['tag'] = $slider_tag;
}

$slider_posts = new WP_Query($slider_args);

if ($slider_posts->have_posts()) :
    $total_slides = $slider_posts->post_count;
    $slider_id = 'news-slider-' . uniqid();
    ?>
    
    <div class="news-slider" id="<?php echo esc_attr($slider_id); ?>"
         data-autoplay="<?php echo esc_attr($slider_autoplay ? 'true' : 'false'); ?>"
         data-speed="<?php echo esc_attr($slider_autoplay_speed); ?>"
         data-animation="<?php echo esc_attr($slider_animation); ?>"
         data-total="<?php echo esc_attr($total_slides); ?>">
        
        <div class="slider-container" role="region" aria-label="<?php esc_attr_e('News slider', 'newscore'); ?>">
            <?php 
            $slide_index = 0;
            while ($slider_posts->have_posts()) : 
                $slider_posts->the_post();
                $slide_index++;
                $post_id = get_the_ID();
                $slide_class = ($slide_index === 1) ? 'slide active' : 'slide';
                $categories = get_the_category();
                $primary_category = !empty($categories) ? $categories[0] : null;
                
                // Получаем данные поста
                $post_title = get_the_title();
                $post_excerpt = get_the_excerpt();
                $post_date = get_the_date();
                $post_author = get_the_author();
                $post_link = get_permalink();
                ?>
                
                <div class="<?php echo esc_attr($slide_class); ?>" 
                     data-slide="<?php echo esc_html(); ?>"
                     role="group" 
                     aria-roledescription="slide"
                     aria-label="<?php echo esc_attr(sprintf(__('Slide %d of %d: %s', 'newscore'), 
                         $slide_index, $total_slides, $post_title)); ?>">
                    
                    <div class="slide-image">
                        <a href="<?php echo esc_url($post_link); ?>" 
                           aria-label="<?php echo esc_attr($post_title); ?>">
                            <?php if (has_post_thumbnail()) : 
                                the_post_thumbnail('newscore-large', array(
                                    'alt' => $post_title,
                                    'loading' => $slide_index <= 2 ? 'eager' : 'lazy'
                                ));
                            else : ?>
                                <div class="slide-image-placeholder">
                                    <span class="placeholder-text"><?php esc_html_e('No image', 'newscore'); ?></span>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="slide-content">
                        <?php if ($primary_category) : ?>
                            <div class="slide-category">
                                <a href="<?php echo esc_url(get_category_link($primary_category->term_id)); ?>" 
                                   class="category-link">
                                    <?php echo esc_html($primary_category->name); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="slide-title">
                            <a href="<?php echo esc_url($post_link); ?>">
                                <?php echo esc_html($post_title); ?>
                            </a>
                        </h3>
                        
                        <?php if (!empty($post_excerpt)) : ?>
                            <div class="slide-excerpt">
                                <?php echo wp_trim_words(esc_html($post_excerpt), 15); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="slide-meta">
                            <span class="slide-date">
                                <time datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo esc_html($post_date); ?>
                                </time>
                            </span>
                            <span class="slide-author">
                                <?php 
                                echo esc_html__('by', 'newscore') . ' ';
                                echo esc_html($post_author);
                                ?>
                            </span>
                            
                            <?php 
                            $reading_time = newscore_reading_time();
                            if ($reading_time) : ?>
                                <span class="reading-time">
                                    <?php echo esc_html($reading_time); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="<?php echo esc_url($post_link); ?>" class="slide-read-more">
                            <?php esc_html_e('Read More', 'newscore'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                 aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                    
                    <div class="slide-overlay"></div>
                </div>
                
            <?php endwhile; ?>
        </div>
        
        <div class="slider-controls">
            <button class="slider-prev" 
                    aria-label="<?php esc_attr_e('Previous slide', 'newscore'); ?>"
                    aria-controls="<?php echo esc_attr($slider_id); ?>">
                <span class="screen-reader-text"><?php esc_html_e('Previous', 'newscore'); ?></span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                     aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>
            
            <div class="slider-dots" role="tablist" aria-label="<?php esc_attr_e('Slide navigation dots', 'newscore'); ?>">
                <?php for ($i = 1; $i <= $total_slides; $i++) : ?>
                    <button class="slider-dot <?php echo $i === 1 ? 'active' : ''; ?>" 
                            data-slide="<?php echo esc_html(); ?>"
                            role="tab"
                            aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'newscore'), $i)); ?>"
                            aria-selected="<?php echo $i === 1 ? 'true' : 'false'; ?>"
                            aria-controls="<?php echo esc_attr($slider_id); ?>">
                        <span class="screen-reader-text">
                            <?php printf(esc_html__('Slide %d', 'newscore'), $i); ?>
                        </span>
                    </button>
                <?php endfor; ?>
            </div>
            
            <button class="slider-next" 
                    aria-label="<?php esc_attr_e('Next slide', 'newscore'); ?>"
                    aria-controls="<?php echo esc_attr($slider_id); ?>">
                <span class="screen-reader-text"><?php esc_html_e('Next', 'newscore'); ?></span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                     aria-hidden="true">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>
        
        <?php if ($slider_autoplay) : ?>
            <div class="slider-autoplay-controls">
                <button class="autoplay-toggle" 
                        aria-label="<?php esc_attr_e('Toggle autoplay', 'newscore'); ?>"
                        data-playing="true">
                    <span class="pause-icon" aria-hidden="true">⏸</span>
                    <span class="play-icon" aria-hidden="true" style="display: none;">▶</span>
                    <span class="screen-reader-text"><?php esc_html_e('Pause autoplay', 'newscore'); ?></span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="slider-progress" role="presentation">
            <div class="progress-bar"></div>
        </div>
    </div>
    
    <?php
    wp_reset_postdata();
    
    // Если слайдер пустой и пользователь может редактировать
elseif (current_user_can('edit_posts')) :
    ?>
    <div class="slider-empty-notice">
        <p><?php esc_html_e('No posts found for the slider. Please add posts with featured images.', 'newscore'); ?></p>
        <a href="<?php echo admin_url('post-new.php'); ?>" class="button">
            <?php esc_html_e('Add New Post', 'newscore'); ?>
        </a>
    </div>
    <?php
endif;

// Функция расчета времени чтения
if (!function_exists('newscore_reading_time')) :
    function newscore_reading_time() {
        $post_content = get_post_field('post_content', get_the_ID());
        $word_count = str_word_count(strip_tags($post_content));
        $reading_time = ceil($word_count / 200); // 200 слов в минуту
        
        if ($reading_time < 1) {
            return esc_html__('Less than 1 min read', 'newscore');
        } else {
            return sprintf(
                esc_html(_n('%d min read', '%d mins read', $reading_time, 'newscore')),
                $reading_time
            );
        }
    }
endif;
?>