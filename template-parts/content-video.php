<?php
/**
 * Content template for video posts
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('post-card video-post'); ?>>
    <div class="post-thumbnail video-thumbnail">
        <?php
        // Пробуем получить видео
        $video_url = get_post_meta(get_the_ID(), 'video_url', true);
        
        if ($video_url) {
            echo wp_oembed_get($video_url);
        } elseif (has_post_thumbnail()) {
            the_post_thumbnail('newscore-medium');
            echo '<div class="play-button"></div>';
        } else {
            echo '<img src="' . get_template_directory_uri() . '/assets/images/video-thumb.jpg" alt="' . get_the_title() . '">';
            echo '<div class="play-button"></div>';
        }
        ?>
        
        <div class="post-category-badge">
            <?php
            $categories = get_the_category();
            if (!empty($categories)) {
                echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">';
                echo esc_html($categories[0]->name);
                echo '</a>';
            }
            ?>
        </div>
        
        <div class="video-duration">
            <?php
            $duration = get_post_meta(get_the_ID(), 'video_duration', true);
            echo $duration ? esc_html($duration) : '--:--';
            ?>
        </div>
    </div>
    
    <div class="post-content">
        <header class="post-header">
            <div class="post-meta">
                <span class="post-date">
                    <?php echo get_the_date(); ?>
                </span>
                <span class="video-views">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <?php
                    $views = get_post_meta(get_the_ID(), 'video_views', true);
                    echo $views ? newscore_format_number($views) : '0';
                    ?>
                </span>
            </div>
            
            <h3 class="post-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
        </header>
        
        <div class="post-excerpt">
            <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
        </div>
        
        <div class="post-footer">
            <div class="post-author">
                <?php echo get_avatar(get_the_author_meta('ID'), 30); ?>
                <span class="author-name">
                    <?php echo get_the_author(); ?>
                </span>
            </div>
        </div>
    </div>
</article>