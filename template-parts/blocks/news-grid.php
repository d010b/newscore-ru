<?php
/**
 * News grid by categories
 */
$categories = get_categories(array(
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => true,
    'number' => 3 // Показываем 3 категории на главной
));

foreach ($categories as $category) :
    $args = array(
        'posts_per_page' => 4,
        'cat' => $category->term_id,
        'ignore_sticky_posts' => 1
    );
    
    $category_posts = new WP_Query($args);
    
    if ($category_posts->have_posts()) :
?>
<section class="category-section category-<?php echo $category->slug; ?>">
    <div class="section-header">
        <h2 class="section-title">
            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                <?php echo esc_html($category->name); ?>
            </a>
        </h2>
        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="section-more">
            <?php esc_html_e('View All', 'newscore'); ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    
    <div class="category-posts-grid">
        <?php
        $post_count = 0;
        while ($category_posts->have_posts()) : $category_posts->the_post();
            $post_count++;
            
            if ($post_count === 1) :
        ?>
                <article class="category-post category-post-large">
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('newscore-medium'); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-thumb.jpg" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="post-content">
                        <h3 class="post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <div class="post-excerpt">
                            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                        </div>
                        
                        <div class="post-meta">
                            <span class="post-date"><?php echo get_the_date(); ?></span>
                            <span class="post-author"><?php the_author(); ?></span>
                        </div>
                    </div>
                </article>
                
                <div class="category-posts-list">
            <?php else : ?>
                    <article class="category-post-item">
                        <div class="post-thumbnail-small">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('newscore-small'); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-thumb-small.jpg" alt="<?php the_title(); ?>">
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <div class="post-content-small">
                            <h4 class="post-title-small">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            
                            <div class="post-meta-small">
                                <span class="post-date"><?php echo get_the_date('d.m.Y'); ?></span>
                            </div>
                        </div>
                    </article>
            <?php
            endif;
        endwhile;
        ?>
                </div><!-- .category-posts-list -->
    </div><!-- .category-posts-grid -->
</section>
<?php
    endif;
    wp_reset_postdata();
endforeach;
?>