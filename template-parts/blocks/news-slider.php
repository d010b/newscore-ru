<?php
/**
 * News slider template
 */
$slider_posts = new WP_Query(array(
    'posts_per_page' => 5,
    'meta_key' => '_slider_post',
    'meta_value' => '1',
    'orderby' => 'date',
    'order' => 'DESC'
));

if ($slider_posts->have_posts()) :
?>
<div class="news-slider">
    <div class="slider-container">
        <?php while ($slider_posts->have_posts()) : $slider_posts->the_post(); ?>
            <div class="slide">
                <div class="slide-image">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('newscore-large'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="slide-content">
                    <div class="slide-category">
                        <?php
                        $categories = get_the_category();
                        if (!empty($categories)) {
                            echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                        }
                        ?>
                    </div>
                    <h3 class="slide-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <div class="slide-meta">
                        <span class="slide-date"><?php echo get_the_date(); ?></span>
                        <span class="slide-author"><?php the_author(); ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    
    <div class="slider-controls">
        <button class="slider-prev" aria-label="<?php esc_attr_e('Previous slide', 'newscore'); ?>">
            <span class="screen-reader-text"><?php esc_html_e('Previous', 'newscore'); ?></span>
        </button>
        <div class="slider-dots"></div>
        <button class="slider-next" aria-label="<?php esc_attr_e('Next slide', 'newscore'); ?>">
            <span class="screen-reader-text"><?php esc_html_e('Next', 'newscore'); ?></span>
        </button>
    </div>
</div>
<?php endif; ?>