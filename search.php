<?php
/**
 * Search results template
 */
get_header();
?>

<main id="primary" class="site-main search-results">
    <div class="container">
        <div class="content-area">
            <header class="page-header">
                <h1 class="page-title">
                    <?php
                    printf(
                        esc_html__('Search Results for: %s', 'newscore'),
                        '<span>' . get_search_query() . '</span>'
                    );
                    ?>
                </h1>
            </header>
            
            <div class="search-info">
                <p><?php
                    global $wp_query;
                    printf(
                        esc_html__('Found %d results', 'newscore'),
                        $wp_query->found_posts
                    );
                ?></p>
            </div>
            
            <?php if (have_posts()) : ?>
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article <?php post_class('search-result'); ?>>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('newscore-medium'); ?>
                                    <?php else : ?>
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-thumb.jpg" alt="<?php echo esc_html(get_the_title()); ?>">
                                    <?php endif; ?>
                                </a>
                            </div>
                            
                            <div class="post-content">
                                <header class="post-header">
                                    <div class="post-meta">
                                        <span class="post-category">
                                            <?php
                                            $categories = get_the_category();
                                            if (!empty($categories)) {
                                                echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                                            }
                                            ?>
                                        </span>
                                        <span class="post-date"><?php echo get_the_date(); ?></span>
                                    </div>
                                    
                                    <h2 class="post-title">
                                        <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                    </h2>
                                </header>
                                
                                <div class="post-excerpt">
                                    <?php
                                    $excerpt = get_the_excerpt();
                                    $search_query = get_search_query();
                                    if ($search_query) {
                                        $excerpt = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<mark>$1</mark>', $excerpt);
                                    }
                                    echo $excerpt;
                                    ?>
                                </div>
                                
                                <div class="search-result-link">
                                    <a href="<?php the_permalink(); ?>" class="read-more">
                                        <?php esc_html_e('Read More', 'newscore'); ?>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="pagination-wrapper">
                    <?php newscore_pagination(); ?>
                </div>
                
            <?php else : ?>
                <div class="no-results">
                    <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'newscore'); ?></p>
                    
                    <div class="search-form-wrapper">
                        <?php get_search_form(); ?>
                    </div>
                    
                    <div class="popular-categories">
                        <h3><?php esc_html_e('Popular Categories:', 'newscore'); ?></h3>
                        <div class="categories-list">
                            <?php
                            $categories = get_categories(array(
                                'orderby' => 'count',
                                'order' => 'DESC',
                                'number' => 6
                            ));
                            
                            foreach ($categories as $category) {
                                echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="category-link">' . esc_html($category->name) . ' (' . $category->count . ')</a>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php get_sidebar(); ?>
    </div>
</main>

<?php
get_footer();
?>