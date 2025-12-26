<?php
/**
 * Main template file
 */
get_header();
?>

<main id="primary" class="site-main">
    <div class="content-area">
        <?php if (have_posts()) : ?>
            <div class="posts-grid">
                <?php
                $count = 0;
                while (have_posts()) :
                    the_post();
                    $count++;
                    
                    // Разный HTML для первого поста
                    if ($count === 1 && is_home()) {
                        echo '<article class="post-featured">';
                    } else {
                        echo '<article class="post-standard">';
                    }
                ?>
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php
                            if (has_post_thumbnail()) {
                                the_post_thumbnail($count === 1 ? 'newscore-large' : 'newscore-medium');
                            } else {
                                echo '<img src="' . get_template_directory_uri() . '/assets/images/default-thumb.jpg" alt="' . get_the_title() . '">';
                            }
                            ?>
                        </a>
                        <?php if ($count === 1) : ?>
                            <div class="post-category">
                                <?php
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-content">
                        <header class="post-header">
                            <?php if ($count > 1) : ?>
                                <div class="post-meta">
                                    <span class="post-category">
                                        <?php
                                        $categories = get_the_category();
                                        if (!empty($categories)) {
                                            echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                                        }
                                        ?>
                                    </span>
                                    <span class="post-date"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . __('ago', 'newscore'); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            </h2>
                            
                            <?php if ($count === 1) : ?>
                                <div class="post-meta">
                                    <span class="post-author">
                                        <?php esc_html_e('By', 'newscore'); ?> 
                                        <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                            <?php the_author(); ?>
                                        </a>
                                    </span>
                                    <span class="post-date"><?php echo get_the_date(); ?></span>
                                    <span class="reading-time"><?php echo newscore_reading_time(); ?></span>
                                </div>
                            <?php endif; ?>
                        </header>
                        
                        <div class="post-excerpt">
                            <?php
                            if ($count === 1) {
                                the_excerpt();
                            } else {
                                echo wp_trim_words(get_the_excerpt(), 20);
                            }
                            ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            
            <div class="pagination-wrapper">
                <?php newscore_pagination(); ?>
            </div>
            
        <?php else : ?>
            <?php get_template_part('template-parts/content', 'none'); ?>
        <?php endif; ?>
    </div>
    
    <?php get_sidebar(); ?>
</main>

<?php
get_footer();
?>