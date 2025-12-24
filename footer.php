</div><!-- .container -->
    </div><!-- #content -->
    
    <footer id="colophon" class="site-footer">
        <div class="footer-widgets">
            <div class="container">
                <div class="footer-widgets-grid">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-2')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-2'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-3')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-3'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (is_active_sidebar('footer-4')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-4'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-menu">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_id' => 'footer-menu',
                        'container' => 'nav',
                        'depth' => 1,
                    ));
                    ?>
                </div>
                
                <div class="site-info">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. 
                    <?php esc_html_e('All rights reserved.', 'newscore'); ?></p>
                    <p>
                        <a href="<?php echo esc_url(__('/privacy-policy/', 'newscore')); ?>">
                            <?php esc_html_e('Privacy Policy', 'newscore'); ?>
                        </a> | 
                        <a href="<?php echo esc_url(__('/terms-of-use/', 'newscore')); ?>">
                            <?php esc_html_e('Terms of Use', 'newscore'); ?>
                        </a>
                    </p>
                </div>
                
                <div class="social-links">
                    <?php
                    $socials = array('facebook', 'twitter', 'instagram', 'youtube', 'telegram');
                    foreach ($socials as $social) {
                        $url = get_theme_mod($social . '_url');
                        if ($url) {
                            echo '<a href="' . esc_url($url) . '" class="social-link ' . esc_attr($social) . '" target="_blank" rel="noopener">';
                            echo '<span class="screen-reader-text">' . ucfirst($social) . '</span>';
                            echo '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </footer>
    
    <?php if (get_theme_mod('show_back_to_top', true)) : ?>
    <a href="#page" class="back-to-top" aria-label="<?php esc_attr_e('Back to top', 'newscore'); ?>">
        ↑
    </a>
    <?php endif; ?>
    
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>