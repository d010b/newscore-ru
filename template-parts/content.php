<?php
/**
 * Content template for posts
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
    <div class="post-thumbnail">
        <a href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('newscore-medium'); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-thumb.jpg" alt="<?php the_title(); ?>">
            <?php endif; ?>
        </a>
        <?php
        $categories = get_the_category();
        if (!empty($categories)) :
        ?>
            <div class="post-category-badge">
                <a href="<?php echo esc_url(get_category_link($categories[0]->term_id)); ?>">
                    <?php echo esc_html($categories[0]->name); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="post-content">
        <header class="post-header">
            <div class="post-meta">
                <span class="post-date">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . esc_html__('ago', 'newscore'); ?>
                </span>
                
                <?php if (get_comments_number() > 0) : ?>
                    <span class="post-comments">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        <?php echo get_comments_number(); ?>
                    </span>
                <?php endif; ?>
                
                <span class="post-views">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <?php
                    $views = get_post_meta(get_the_ID(), 'post_views', true);
                    echo $views ? newscore_format_number($views) : '0';
                    ?>
                </span>
            </div>
            
            <h3 class="post-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
        </header>
        
        <div class="post-excerpt">
            <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
        </div>
        
        <div class="post-footer">
            <div class="post-author">
                <?php echo get_avatar(get_the_author_meta('ID'), 30); ?>
                <span class="author-name">
                    <?php echo get_the_author(); ?>
                </span>
            </div>
            
            <a href="<?php the_permalink(); ?>" class="read-more">
                <?php esc_html_e('Read More', 'newscore'); ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</article>