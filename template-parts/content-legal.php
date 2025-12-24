<?php
/**
 * Template for legal pages (privacy policy, user agreement, etc.)
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('legal-page'); ?>>
    <header class="legal-header">
        <h1 class="legal-title"><?php the_title(); ?></h1>
        
        <div class="legal-meta">
            <?php if (get_field('effective_date')) : ?>
                <span class="effective-date">
                    Дата вступления в силу: <?php echo esc_html(get_field('effective_date')); ?>
                </span>
            <?php endif; ?>
            
            <?php if (get_field('last_updated')) : ?>
                <span class="last-updated">
                    Последнее обновление: <?php echo esc_html(get_field('last_updated')); ?>
                </span>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="legal-content">
        <?php the_content(); ?>
        
        <?php if (get_field('legal_notes')) : ?>
            <div class="legal-notes">
                <h3>Юридические примечания</h3>
                <?php echo wp_kses_post(get_field('legal_notes')); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="legal-footer">
        <div class="legal-contacts">
            <h4>Контакты для юридических вопросов</h4>
            
            <?php if (get_theme_mod('legal_email')) : ?>
                <p>
                    <strong>Email:</strong> 
                    <a href="mailto:<?php echo esc_attr(get_theme_mod('legal_email')); ?>">
                        <?php echo esc_html(get_theme_mod('legal_email')); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <?php if (get_theme_mod('legal_phone')) : ?>
                <p>
                    <strong>Телефон:</strong> 
                    <?php echo esc_html(get_theme_mod('legal_phone')); ?>
                </p>
            <?php endif; ?>
            
            <?php if (get_theme_mod('legal_address')) : ?>
                <p>
                    <strong>Адрес:</strong> 
                    <?php echo esc_html(get_theme_mod('legal_address')); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="legal-download">
            <a href="<?php echo esc_url(add_query_arg('download', 'pdf', get_permalink())); ?>" class="button">
                Скачать PDF версию
            </a>
            
            <a href="javascript:window.print()" class="button">
                Распечатать документ
            </a>
        </div>
    </footer>
</article>