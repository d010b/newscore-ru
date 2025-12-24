<?php
/**
 * Author archive template
 */
get_header();

$author = get_queried_object();
?>

<main id="primary" class="site-main author-archive">
    <div class="container">
        <div class="content-area">
            <header class="page-header author-header">
                <div class="author-info">
                    <div class="author-avatar">
                        <?php echo get_avatar($author->ID, 120); ?>
                    </div>
                    <div class="author-details">
                        <h1 class="page-title"><?php echo esc_html($author->display_name); ?></h1>
                        
                        <?php if ($author->description) : ?>
                            <div class="author-bio">
                                <?php echo wpautop($author->description); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="author-stats">
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('Posts', 'newscore'); ?>:</span>
                                <span class="stat-value"><?php echo count_user_posts($author->ID); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label"><?php esc_html_e('Joined', 'newscore'); ?>:</span>
                                <span class="stat-value"><?php echo date_i18n(get_option('date_format'), strtotime($author->user_registered)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <?php if (have_posts()) : ?>
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/content', get_post_type()); ?>
                    <?php endwhile; ?>
                </div>
                
                <div class="pagination-wrapper">
                    <?php newscore_pagination(); ?>
                </div>
            <?php else : ?>
                <div class="no-posts">
                    <p><?php printf(esc_html__('%s has not published any news yet.', 'newscore'), $author->display_name); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();