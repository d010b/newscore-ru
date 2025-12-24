<?php
/**
 * 404 error page
 */
get_header();
?>

<main id="primary" class="site-main error-404">
    <div class="container">
        <div class="error-content">
            <div class="error-code">
                <h1>404</h1>
                <h2><?php esc_html_e('Page Not Found', 'newscore'); ?></h2>
            </div>
            
            <div class="error-message">
                <p><?php esc_html_e('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'newscore'); ?></p>
                
                <div class="search-form-wrapper">
                    <h3><?php esc_html_e('Try Searching', 'newscore'); ?></h3>
                    <?php get_search_form(); ?>
                </div>
                
                <div class="back-home">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="button">
                        <?php esc_html_e('Back to Homepage', 'newscore'); ?>
                    </a>
                </div>
                
                <div class="popular-posts">
                    <h3><?php esc_html_e('Recent News', 'newscore'); ?></h3>
                    <div class="posts-list">
                        <?php
                        $recent_posts = wp_get_recent_posts(array(
                            'numberposts' => 5,
                            'post_status' => 'publish'
                        ));
                        
                        foreach ($recent_posts as $post) : ?>
                            <div class="post-item">
                                <a href="<?php echo get_permalink($post['ID']); ?>">
                                    <?php echo esc_html($post['post_title']); ?>
                                </a>
                                <span class="post-date"><?php echo get_the_date('', $post['ID']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
?>