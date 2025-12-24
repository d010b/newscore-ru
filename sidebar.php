<?php
/**
 * Sidebar template
 */
if (!is_active_sidebar('sidebar-1') && !is_active_sidebar('sidebar-2')) {
    return;
}
?>

<aside id="secondary" class="widget-area sidebar">
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        <div class="primary-sidebar">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </div>
    <?php endif; ?>
    
    <?php if (is_active_sidebar('sidebar-2') && (is_single() || is_front_page())) : ?>
        <div class="secondary-sidebar">
            <?php dynamic_sidebar('sidebar-2'); ?>
        </div>
    <?php endif; ?>
</aside>