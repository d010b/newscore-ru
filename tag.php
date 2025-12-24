<?php
/**
 * Tag archive template
 */
get_header();
?>

<main id="primary" class="site-main tag-archive">
    <div class="container">
        <div class="content-area">
            <header class="page-header tag-header">
                <h1 class="page-title">
                    <?php
                    printf(
                        esc_html__('Tag: %s', 'newscore'),
                        '<span>' . single_tag_title('', false) . '</span>'
                    );
                    ?>
                </h1>
                
                <?php if (tag_description()) : ?>
                    <div class="tag-description">
                        <?php echo tag_description(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="tag-stats">
                    <span class="stat">
                        <?php
                        $tag = get_queried_object();
                        printf(
                            esc_html__('%d articles', 'newscore'),
                            $tag->count
                        );
                        ?>
                    </span>
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
                <?php get_template_part('template-parts/content', 'none'); ?>
            <?php endif; ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();