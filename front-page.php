<?php
/**
 * Front page template
 */
get_header();
?>

<main id="primary" class="site-main front-page">
    <?php
    // Включаем слайдер главных новостей
    get_template_part('template-parts/blocks/news-slider');
    ?>
    
    <div class="container">
        <div class="content-area">
            <?php
            // Блок главных новостей
            $main_news = new WP_Query(array(
                'posts_per_page' => 1,
                'category_name' => 'main',
                'meta_key' => '_featured_post',
                'meta_value' => '1'
            ));
            
            if ($main_news->have_posts()) :
                while ($main_news->have_posts()) : $main_news->the_post();
            ?>
                <section class="featured-section">
                    <h2 class="section-title"><?php esc_html_e('Main News', 'newscore'); ?></h2>
                    <article class="main-featured-post">
                        <div class="featured-thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('newscore-large'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="featured-content">
                            <div class="post-meta">
                                <span class="post-category">
                                    <?php
                                    $categories = get_the_category();
                                    if (!empty($categories)) {
                                        echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                                    }
                                    ?>
                                </span>
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                            </div>
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            </h3>
                            <div class="post-excerpt">
                                <?php echo esc_html(get_the_excerpt()); ?>
                            </div>
                            <div class="post-meta-bottom">
                                <span class="post-author">
                                    <?php esc_html_e('By', 'newscore'); ?> <?php the_author(); ?>
                                </span>
                                <span class="reading-time"><?php echo newscore_reading_time(); ?></span>
                            </div>
                        </div>
                    </article>
                </section>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
            
            <?php
            // Сетка новостей по категориям
            get_template_part('template-parts/blocks/news-grid');
            ?>
            
            <?php
            // Последние новости
            $recent_posts = new WP_Query(array(
                'posts_per_page' => 6,
                'post__not_in' => get_option('sticky_posts'),
                'paged' => get_query_var('paged') ? get_query_var('paged') : 1
            ));
            
            if ($recent_posts->have_posts()) :
            ?>
                <section class="latest-news">
                    <h2 class="section-title"><?php esc_html_e('Latest News', 'newscore'); ?></h2>
                    <div class="posts-grid">
                        <?php
                        while ($recent_posts->have_posts()) : $recent_posts->the_post();
                            get_template_part('template-parts/content', get_post_type());
                        endwhile;
                        ?>
                    </div>
                    
                    <?php if ($recent_posts->max_num_pages > 1) : ?>
                        <div class="load-more-container">
                            <button class="load-more-btn" data-page="1" data-max="<?php echo $recent_posts->max_num_pages; ?>">
                                <?php esc_html_e('Load More', 'newscore'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </section>
            <?php
                wp_reset_postdata();
            endif;
            ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();
?>