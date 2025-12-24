<?php
/**
 * Template when no content is found
 */
?>
<section class="no-content">
    <div class="no-content-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
    </div>
    
    <h2 class="no-content-title"><?php esc_html_e('Nothing Found', 'newscore'); ?></h2>
    
    <div class="no-content-text">
        <?php if (is_home() && current_user_can('publish_posts')) : ?>
            <p><?php
                printf(
                    esc_html__('Ready to publish your first post? %1$sGet started here%2$s.', 'newscore'),
                    '<a href="' . esc_url(admin_url('post-new.php')) . '">',
                    '</a>'
                );
            ?></p>
        <?php elseif (is_search()) : ?>
            <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'newscore'); ?></p>
        <?php else : ?>
            <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'newscore'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="no-content-search">
        <?php get_search_form(); ?>
    </div>
</section>