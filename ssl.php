<?php // reference: http://yoast.com/wordpress-ssl-setup/

// subscribe page ID: 94

add_action('template_redirect', function () {
    // Enforce SSL on the subscribe page
    if (is_page(94) && ! is_ssl()) {
        if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
            wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']), 301);
        } else {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        }

        exit();
    }
    // Enforce no SSL on the other pages
    elseif (! is_page(94) && is_ssl() && ! is_admin()) {
        if (0 === strpos($_SERVER['REQUEST_URI'], 'http')) {
            wp_redirect(preg_replace('|^https://|', 'http://', $_SERVER['REQUEST_URI']), 301);
        } else {
            wp_redirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
        }

        exit();
    }

}, 1);

// We want get_permalink (and friends) to return the right URL
add_filter('pre_post_link', function ($permalink, $post, $leavename) {
    return $post->ID != 94 ? $permalink : preg_replace('|^http://|', 'https://', $permalink);
}, 10, 3);
