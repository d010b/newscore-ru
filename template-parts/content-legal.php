<?php
/**
 * Template for legal pages (privacy policy, user agreement, etc.)
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('legal-page'); ?>>
    <header class="legal-header">
        <h1 class="legal-title"><?php echo esc_html(get_the_title()); ?></h1>
        <div class="legal-meta">
            <?php 
            // Проверяем наличие функции get_field (ACF плагин)
            if (function_exists('get_field')) :
                $effective_date = get_field('effective_date');
                $last_updated = get_field('last_updated');
                
                if ($effective_date) : ?>
                    <span class="effective-date">
                        Дата вступления в силу: <?php echo esc_html($effective_date); ?>
                    </span>
                <?php endif;
                
                if ($last_updated) : ?>
                    <span class="last-updated">
                        Последнее обновление: <?php echo esc_html($last_updated); ?>
                    </span>
                <?php endif;
            endif;
            ?>
        </div>
    </header>

    <div class="legal-content">
        <?php the_content(); ?>
        
        <?php 
        if (function_exists('get_field')) :
            $legal_notes = get_field('legal_notes');
            if ($legal_notes) : ?>
                <div class="legal-notes">
                    <h3>Юридические примечания</h3>
                    <?php echo wp_kses_post($legal_notes); ?>
                </div>
            <?php endif;
        endif;
        ?>
    </div>

    <footer class="legal-footer">
        <div class="legal-contacts">
            <h4>Контакты для юридических вопросов</h4>
            <?php 
            $legal_email = get_theme_mod('legal_email');
            $legal_phone = get_theme_mod('legal_phone');
            $legal_address = get_theme_mod('legal_address');
            
            if ($legal_email) : ?>
                <p>
                    <strong>Email:</strong>
                    <a href="mailto:<?php echo esc_attr($legal_email); ?>">
                        <?php echo esc_html($legal_email); ?>
                    </a>
                </p>
            <?php endif;
            
            if ($legal_phone) : ?>
                <p>
                    <strong>Телефон:</strong>
                    <?php echo esc_html($legal_phone); ?>
                </p>
            <?php endif;
            
            if ($legal_address) : ?>
                <p>
                    <strong>Адрес:</strong>
                    <?php echo esc_html($legal_address); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="legal-download">
            <?php
            // Генерация PDF ссылки только если установлен плагин для PDF
            if (shortcode_exists('pdf_generator')) : ?>
                <a href="<?php echo esc_url(add_query_arg('download', 'pdf', get_permalink())); ?>" class="button">
                    Скачать PDF версию
                </a>
            <?php endif; ?>
            
            <a href="#" onclick="window.print(); return false;" class="button">
                Распечатать документ
            </a>
        </div>
    </footer>
</article>