<?php
/**
 * Search form template
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="screen-reader-text"><?php esc_html_e('Search for:', 'newscore'); ?></label>
    <div class="search-input-group">
        <input type="search" class="search-field" 
               placeholder="<?php esc_attr_e('Search news...', 'newscore'); ?>" 
               value="<?php echo get_search_query(); ?>" 
               name="s" 
               title="<?php esc_attr_e('Search for:', 'newscore'); ?>" 
               required>
        <button type="submit" class="search-submit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <span class="screen-reader-text"><?php esc_html_e('Search', 'newscore'); ?></span>
        </button>
    </div>
</form>