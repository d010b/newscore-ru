<?php
/**
 * Page template
 */
get_header();
?>

<main id="primary" class="site-main page-template">
    <div class="container">
        <div class="content-area">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="page-thumbnail">
                                <?php the_post_thumbnail('newscore-large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                        
                        <?php if (get_theme_mod('show_page_meta', false)) : ?>
                            <div class="entry-meta">
                                <span class="posted-on">
                                    <?php echo get_the_date(); ?>
                                </span>
                                <?php if (comments_open()) : ?>
                                    <span class="comments-link">
                                        <?php comments_popup_link(
                                            esc_html__('Leave a comment', 'newscore'),
                                            esc_html__('1 Comment', 'newscore'),
                                            esc_html__('% Comments', 'newscore')
                                        ); ?>
                                    </span>
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
                    
                    <?php if (comments_open() || get_comments_number()) : ?>
                        <footer class="entry-footer">
                            <?php comments_template(); ?>
                        </footer>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();
?>