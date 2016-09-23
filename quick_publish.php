<?php

add_filter('post_row_actions', function ($actions) {
    global $post;

    // If the post hasn't been published yet
    if (get_post_status($post) != 'publish') {
        $nonce = wp_create_nonce('quick-publish-action');

        $link = admin_url('admin.php?page=quick_publish_post_action')
                    . "&amp;post_id={$post->ID}&amp;_wpnonce=$nonce";

        $actions['publish'] = "<a href=\"$link\">Publish</a>";
    }

    return $actions;
});

add_action('admin_menu', function () {
    add_submenu_page(
        null,
        'redirect',
        'non-existent',
        'read',
        'quick_publish_post_action',
        function () {
            if (! wp_verify_nonce($_GET['_wpnonce'], 'quick-publish-action')) {
                die('<p class="error-message">Your session has expired.</p>');
            }

            wp_publish_post($_GET['post_id']);

            wp_redirect($_SERVER['HTTP_REFERER'], 302);
        }
    );
});
