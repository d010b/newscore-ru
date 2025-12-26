<?php
/**
 * Category archive template
 */
get_header();

$category = get_queried_object();
?>

<main id="primary" class="site-main category-archive">
    <div class="container">
        <div class="content-area">
            <header class="page-header category-header">
                <h1 class="page-title"><?php single_cat_title(); ?></h1>
                
                <?php if (category_description()) : ?>
                    <div class="category-description">
                        <?php echo category_description(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="category-stats">
                    <span class="stat">
                        <?php printf(
                            esc_html__('%d articles', 'newscore'),
                            $category->count
                        ); ?>
                    </span>
                    <span class="stat">
                        <?php printf(
                            esc_html__('Last update: %s', 'newscore'),
                            get_the_modified_time(get_option('date_format'), $category->term_id)
                        ); ?>
                    </span>
                </div>
            </header>
            
            <?php if (have_posts()) : ?>
                <?php
                // Первый пост выделяем
                if (have_posts()) :
                    the_post();
                ?>
                    <article class="category-featured-post">
                        <div class="featured-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('newscore-large'); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-thumb.jpg" alt="<?php echo esc_html(get_the_title()); ?>">
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <div class="featured-content">
                            <h2 class="featured-title">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            </h2>
                            
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author"><?php the_author(); ?></span>
                                <span class="reading-time"><?php echo newscore_reading_time(); ?></span>
                            </div>
                            
                            <div class="post-excerpt">
                                <?php echo esc_html(get_the_excerpt()); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php esc_html_e('Read Full Article', 'newscore'); ?>
                            </a>
                        </div>
                    </article>
                <?php endif; ?>
                
                <?php if (have_posts()) : ?>
                    <div class="posts-grid category-posts">
                        <?php while (have_posts()) : the_post(); ?>
                            <?php get_template_part('template-parts/content', get_post_type()); ?>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="pagination-wrapper">
                        <?php newscore_pagination(); ?>
                    </div>
                <?php endif; ?>
                
            <?php else : ?>
                <?php get_template_part('template-parts/content', 'none'); ?>
            <?php endif; ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();