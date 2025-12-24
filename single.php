<?php
/**
 * Single post template
 */
get_header();
?>

<main id="primary" class="site-main single-post">
    <div class="content-area">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php
                    $categories = get_the_category();
                    if (!empty($categories)) :
                    ?>
                        <div class="entry-categories">
                            <?php foreach ($categories as $category) : ?>
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                   class="category-badge">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    
                    <div class="entry-meta">
                        <div class="meta-author">
                            <?php echo get_avatar(get_the_author_meta('ID'), 40); ?>
                            <div class="author-info">
                                <span class="byline">
                                    <?php esc_html_e('By', 'newscore'); ?>
                                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                        <?php the_author(); ?>
                                    </a>
                                </span>
                                <span class="posted-on">
                                    <?php echo get_the_date(); ?> 
                                    <?php echo get_the_time(); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="meta-stats">
                            <span class="reading-time">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <?php echo newscore_reading_time(); ?>
                            </span>
                            <span class="comments-count">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                                <?php comments_number('0', '1', '%'); ?>
                            </span>
                            <span class="views-count">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <?php
                                $views = get_post_meta(get_the_ID(), 'post_views', true);
                                echo $views ? number_format_i18n($views) : '0';
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="featured-image">
                            <?php the_post_thumbnail('newscore-large'); ?>
                            <?php if (get_the_post_thumbnail_caption()) : ?>
                                <figcaption class="wp-caption-text">
                                    <?php the_post_thumbnail_caption(); ?>
                                </figcaption>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                    
                    <?php
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'newscore'),
                        'after' => '</div>',
                    ));
                    ?>
                </div>
                
                <footer class="entry-footer">
                    <div class="post-tags">
                        <?php
                        $tags = get_the_tags();
                        if ($tags) {
                            echo '<div class="tags-links">';
                            echo '<span class="tags-label">' . esc_html__('Tags:', 'newscore') . '</span> ';
                            foreach ($tags as $tag) {
                                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="tag-link">' . esc_html($tag->name) . '</a>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <div class="post-share">
                        <span class="share-label"><?php esc_html_e('Share:', 'newscore'); ?></span>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" class="share-btn facebook">
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" class="share-btn twitter">
                                Twitter
                            </a>
                            <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" class="share-btn telegram">
                                Telegram
                            </a>
                        </div>
                    </div>
                    
                    <div class="author-box">
                        <div class="author-avatar">
                            <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
                        </div>
                        <div class="author-info">
                            <h4 class="author-name">
                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                    <?php the_author(); ?>
                                </a>
                            </h4>
                            <p class="author-bio"><?php echo get_the_author_meta('description'); ?></p>
                        </div>
                    </div>
                    
                    <?php
                    // Похожие новости
                    $related_posts = get_posts(array(
                        'category__in' => wp_get_post_categories(get_the_ID()),
                        'numberposts' => 3,
                        'post__not_in' => array(get_the_ID()),
                        'orderby' => 'rand'
                    ));
                    
                    if ($related_posts) :
                    ?>
                        <div class="related-posts">
                            <h3 class="related-title"><?php esc_html_e('Related News', 'newscore'); ?></h3>
                            <div class="related-grid">
                                <?php foreach ($related_posts as $post) : setup_postdata($post); ?>
                                    <article class="related-post">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <div class="related-thumbnail">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_post_thumbnail('newscore-small'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="related-content">
                                            <h4 class="related-post-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h4>
                                            <div class="related-meta">
                                                <span class="related-date"><?php echo get_the_date(); ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; wp_reset_postdata(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </footer>
            </article>
            
            <?php
            // Комментарии
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
            
        <?php endwhile; ?>
    </div>
    
    <?php get_sidebar(); ?>
</main>

<?php
get_footer();
?>