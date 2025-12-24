<?php
/**
 * Comments template
 */
if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            if ('1' === $comments_number) {
                printf(esc_html__('One Comment', 'newscore'));
            } else {
                printf(
                    esc_html(_n('%s Comment', '%s Comments', $comments_number, 'newscore')),
                    number_format_i18n($comments_number)
                );
            }
            ?>
        </h2>
        
        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
                'avatar_size' => 50,
                'callback' => 'newscore_comment'
            ));
            ?>
        </ol>
        
        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation" role="navigation">
                <div class="nav-previous">
                    <?php previous_comments_link(esc_html__('&larr; Older Comments', 'newscore')); ?>
                </div>
                <div class="nav-next">
                    <?php next_comments_link(esc_html__('Newer Comments &rarr;', 'newscore')); ?>
                </div>
            </nav>
        <?php endif; ?>
        
    <?php endif; ?>
    
    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments"><?php esc_html_e('Comments are closed.', 'newscore'); ?></p>
    <?php endif; ?>
    
    <?php
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $aria_req = $req ? " aria-required='true'" : '';
    
    $fields = array(
        'author' => '<div class="comment-form-author">' .
            '<input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) .
            '" size="30"' . $aria_req . ' placeholder="' . esc_attr__('Name', 'newscore') . ($req ? '*' : '') . '" /></div>',
        'email' => '<div class="comment-form-email">' .
            '<input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) .
            '" size="30"' . $aria_req . ' placeholder="' . esc_attr__('Email', 'newscore') . ($req ? '*' : '') . '" /></div>',
        'url' => '<div class="comment-form-url">' .
            '<input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) .
            '" size="30" placeholder="' . esc_attr__('Website', 'newscore') . '" /></div>',
    );
    
    $args = array(
        'title_reply' => esc_html__('Leave a Comment', 'newscore'),
        'title_reply_to' => esc_html__('Leave a Reply to %s', 'newscore'),
        'cancel_reply_link' => esc_html__('Cancel Reply', 'newscore'),
        'label_submit' => esc_html__('Post Comment', 'newscore'),
        'comment_field' => '<div class="comment-form-comment">' .
            '<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="' . esc_attr__('Comment', 'newscore') . '*"></textarea></div>',
        'fields' => apply_filters('comment_form_default_fields', $fields),
        'class_submit' => 'submit button',
    );
    
    comment_form($args);
    ?>
</div>