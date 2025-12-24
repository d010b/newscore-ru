<?php
/**
 * Blog posts index (when front page is static)
 */
get_header();
?>

<main id="primary" class="site-main blog-index">
    <div class="container">
        <div class="content-area">
            <?php if (have_posts()) : ?>
                <header class="page-header">
                    <h1 class="page-title"><?php bloginfo('name'); ?> - <?php esc_html_e('Latest News', 'newscore'); ?></h1>
                </header>
                
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/content', get_post_type()); ?>
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
    </div>
</main>

<?php
get_footer();