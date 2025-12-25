<?php
/** 
 * News grid by categories with flexible settings 
 */

// Получаем настройки из кастомайзера или используем значения по умолчанию
$categories_count = get_theme_mod('home_categories_count', 3);
$posts_per_category = get_theme_mod('posts_per_category', 4);
$exclude_categories = get_theme_mod('exclude_categories', '');

$cat_args = array(
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => true,
    'number' => $categories_count
);

// Исключаем категории если заданы
if (!empty($exclude_categories)) {
    $cat_args['exclude'] = array_map('intval', explode(',', $exclude_categories));
}

$categories = get_categories($cat_args);

// Применяем фильтр для безопасности
$categories = apply_filters('newscore_grid_categories', $categories);

if (!empty($categories) && is_array($categories)) : 
    foreach ($categories as $category) :
        // Безопасная проверка категории
        if (!$category || !is_object($category) || empty($category->term_id)) {
            continue;
        }
        
        $args = array(
            'posts_per_page' => $posts_per_category,
            'cat' => intval($category->term_id),
            'ignore_sticky_posts' => 1,
            'no_found_rows' => true,
            'post_status' => 'publish'
        );
        
        $category_posts = new WP_Query($args);
        
        if ($category_posts->have_posts()) : 
            // БЕЗОПАСНОЕ создание переменных
            $category_class = 'category-' . (!empty($category->slug) ? sanitize_html_class($category->slug) : 'uncategorized');
            $category_link = !empty($category->term_id) ? esc_url(get_category_link($category->term_id)) : '#';
            $category_name = !empty($category->name) ? esc_html($category->name) : esc_html__('Без названия', 'newscore');
            $category_description = category_description($category->term_id);
            
            // Проверка на ошибку ссылки
            if (is_wp_error($category_link)) {
                $category_link = '#';
            }
?>
            
            <section class="category-section <?php echo $category_class; ?>">
                <div class="section-header">
                    <h2 class="section-title">
                        <a href="<?php echo $category_link; ?>">
                            <?php echo $category_name; ?>
                        </a>
                    </h2>
                    
                    <?php if ($category_description) : ?>
                        <div class="category-description">
                            <?php echo wp_kses_post($category_description); ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo $category_link; ?>" class="section-more" aria-label="<?php 
                        echo esc_attr(sprintf(__('View all posts in %s', 'newscore'), $category_name)); 
                    ?>">
                        <?php esc_html_e('View All', 'newscore'); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                             aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                
                <div class="category-posts-grid">
                    <?php
                    $post_count = 0;
                    while ($category_posts->have_posts()) : 
                        $category_posts->the_post();
                        $post_count++;
                        $post_id = get_the_ID();
                        $post_class = ($post_count === 1) ? 'category-post category-post-large' : 'category-post-item';
                        
                        if ($post_count === 1) :
                            // Первый пост - большой
                            ?>
                            <article class="category-post category-post-large" data-post-id="<?php echo $post_id; ?>">
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                                        <?php if (has_post_thumbnail()) : 
                                            the_post_thumbnail('newscore-medium', array(
                                                'alt' => get_the_title(),
                                                'loading' => $post_count === 1 ? 'eager' : 'lazy'
                                            ));
                                        else : ?>
                                            <img src="<?php 
                                                echo esc_url(get_template_directory_uri() . '/assets/images/default-thumb.jpg'); 
                                            ?>" alt="<?php the_title_attribute(); ?>" 
                                            loading="<?php echo $post_count === 1 ? 'eager' : 'lazy'; ?>">
                                        <?php endif; ?>
                                    </a>
                                    <?php newscore_post_category_badge($category->term_id); ?>
                                </div>
                                
                                <div class="post-content">
                                    <h3 class="post-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>
                                    
                                    <div class="post-excerpt">
                                        <?php 
                                        $excerpt = get_the_excerpt();
                                        if (empty($excerpt)) {
                                            $excerpt = wp_trim_words(get_the_content(), 20);
                                        }
                                        echo esc_html($excerpt);
                                        ?>
                                    </div>
                                    
                                    <div class="post-meta">
                                        <span class="post-date">
                                            <time datetime="<?php echo get_the_date('c'); ?>">
                                                <?php echo get_the_date(); ?>
                                            </time>
                                        </span>
                                        <span class="post-author">
                                            <?php 
                                            echo esc_html__('By', 'newscore') . ' ';
                                            the_author(); 
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                            
                            <div class="category-posts-list">
                        <?php else : 
                            // Остальные посты - маленькие
                            ?>
                            <article class="category-post-item" data-post-id="<?php echo $post_id; ?>">
                                <div class="post-thumbnail-small">
                                    <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                                        <?php if (has_post_thumbnail()) : 
                                            the_post_thumbnail('newscore-small', array(
                                                'alt' => get_the_title(),
                                                'loading' => 'lazy'
                                            ));
                                        else : ?>
                                            <img src="<?php 
                                                echo esc_url(get_template_directory_uri() . '/assets/images/default-thumb-small.jpg'); 
                                            ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                
                                <div class="post-content-small">
                                    <h4 class="post-title-small">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h4>
                                    
                                    <div class="post-meta-small">
                                        <span class="post-date">
                                            <time datetime="<?php echo get_the_date('c'); ?>">
                                                <?php echo get_the_date('d.m.Y'); ?>
                                            </time>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endif;
                        
                    endwhile; 
                    ?>
                    </div><!-- .category-posts-list -->
                </div><!-- .category-posts-grid -->
                
                <?php if ($category_posts->found_posts > $posts_per_category) : ?>
                    <div class="category-view-more">
                        <a href="<?php echo $category_link; ?>" class="button">
                            <?php 
                            printf(
                                esc_html__('View all %d posts', 'newscore'),
                                $category->count
                            );
                            ?>
                        </a>
                    </div>
                <?php endif; ?>
            </section>
            
            <?php
            wp_reset_postdata();
        endif;
    endforeach;
else :
    // Если нет категорий, показываем сообщение
    if (current_user_can('edit_theme_options')) :
        ?>
        <div class="no-categories-notice">
            <p><?php esc_html_e('No categories found. Please add some categories or adjust the theme settings.', 'newscore'); ?></p>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=category'); ?>" class="button">
                <?php esc_html_e('Manage Categories', 'newscore'); ?>
            </a>
        </div>
        <?php
    endif;
endif;

// Функция для отображения бейджа категории
if (!function_exists('newscore_post_category_badge')) :
    function newscore_post_category_badge($category_id = null) {
        if (!$category_id) {
            $categories = get_the_category();
            if (!empty($categories)) {
                $category_id = $categories[0]->term_id;
            }
        }
        
        if ($category_id) {
            $category = get_category($category_id);
            if ($category && !is_wp_error($category)) {
                $category_link = get_category_link($category_id);
                echo '<div class="post-category-badge">';
                echo '<a href="' . esc_url($category_link) . '" class="category-link">';
                echo esc_html($category->name);
                echo '</a>';
                echo '</div>';
            }
        }
    }
endif;
?>