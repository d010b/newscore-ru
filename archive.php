<?php
/**
 * Archive template
 */
get_header();
?>

<main id="primary" class="site-main archive-page">
    <div class="container">
        <div class="content-area">
            <header class="page-header">
                <?php
                the_archive_title('<h1 class="page-title">', '</h1>');
                the_archive_description('<div class="archive-description">', '</div>');
                ?>
            </header>
            
            <?php if (have_posts()) : ?>
                <div class="posts-grid archive-grid">
                    <?php
                    while (have_posts()) : the_post();
                        get_template_part('template-parts/content', get_post_type());
                    endwhile;
                    ?>
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
?>