<?php
/**
 * Image attachment template
 */
get_header();
?>

<main id="primary" class="site-main attachment-page">
    <div class="container">
        <div class="content-area">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                        
                        <div class="entry-meta">
                            <span class="posted-on">
                                <?php echo get_the_date(); ?>
                            </span>
                            <span class="full-size-link">
                                <a href="<?php echo wp_get_attachment_url(); ?>">
                                    <?php esc_html_e('View full size', 'newscore'); ?>
                                </a>
                            </span>
                        </div>
                    </header>
                    
                    <div class="entry-content">
                        <div class="attachment-container">
                            <?php
                            $image_size = apply_filters('newscore_attachment_size', 'large');
                            echo wp_get_attachment_image(get_the_ID(), $image_size);
                            ?>
                            
                            <?php if (has_excerpt()) : ?>
                                <div class="attachment-caption">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php the_content(); ?>
                            
                            <nav class="image-navigation" role="navigation">
                                <div class="nav-links">
                                    <div class="nav-previous">
                                        <?php previous_image_link(false, '<span class="meta-nav">&larr;</span> ' . esc_html__('Previous Image', 'newscore')); ?>
                                    </div>
                                    <div class="nav-next">
                                        <?php next_image_link(false, esc_html__('Next Image', 'newscore') . ' <span class="meta-nav">&rarr;</span>'); ?>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                    
                    <footer class="entry-footer">
                        <div class="attachment-meta">
                            <?php
                            $metadata = wp_get_attachment_metadata();
                            if ($metadata) {
                                printf(
                                    '<span class="full-size-link"><a href="%s">%s</a></span>',
                                    esc_url(wp_get_attachment_url()),
                                    esc_html__('Full resolution', 'newscore')
                                );
                                
                                printf(
                                    '<span class="image-dimensions">%s &times; %s</span>',
                                    $metadata['width'],
                                    $metadata['height']
                                );
                            }
                            ?>
                        </div>
                        
                        <?php
                        // Ссылка на родительский пост
                        if (!empty($post->post_parent)) {
                            printf(
                                '<div class="parent-post-link">' . esc_html__('Published in %s', 'newscore') . '</div>',
                                sprintf(
                                    '<a href="%s">%s</a>',
                                    esc_url(get_permalink($post->post_parent)),
                                    get_the_title($post->post_parent)
                                )
                            );
                        }
                        ?>
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
    </div>
</main>

<?php
get_footer();