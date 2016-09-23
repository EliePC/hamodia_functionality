<?php
/*
1. I don't want the admin bar on the frontend!
2. Italicise the_title where content is wrapped in asterisks
3. Control the_exceprt
4. Get the caption text as the excerpt for image post type
5. Digital edition `posts_per_page` should be 48
6. Remove hero image article from main query
*/

// If we're in production, let's set up the correct HTTPS configuration
if ($_SERVER['HTTP_HOST'] == 'hamodia.com') {
    include_once('ssl.php');
}

include_once('APIs.php');
include_once('sefirah.php');
include_once('helpers.php');
include_once('functions.php');
include_once('sidebar.php');
include_once('the_content.php');
include_once('the_thumbnail.php');
include_once('category.php');
include_once('gallery.php');

add_action('wp', function () {
    if (is_front_page()) {
        include_once('homepage.php');
    }
});




// 1. I don't want the admin bar on the frontend
add_filter('show_admin_bar', '__return_false');
// and I don't want the Wordpress version number broadcasted
remove_action('wp_head', 'wp_generator');

// 2. Italicise the_title where content is wrapped in asterisks
add_filter('the_title', function ($title) {
    return preg_replace("/\*([^*]+)\*/", "<i>$1</i>", $title);
});

// 3. Control the_exceprt
add_filter('excerpt_more', function ($more) {
    global $post;
    return is_front_page() ? '&hellip;</p>' : '&hellip;</p><p class="read-more"><a href="'.get_permalink($post->ID).'">Read more &raquo;</a></p>';
});
add_filter('excerpt_length', function ($length) {
    return is_search() ? 100 : 30;
},
999);
// 4. Get the caption text as the excerpt for image post type
add_filter('get_the_excerpt', function ($excerpt) {
    if (! has_post_format('image')) {
        return $excerpt;
    }

    // Let's try to get the caption text
    preg_match('~\[caption.*?caption="([^\]]+)"]~', get_the_content(), $matches);

    // if we found nothing, let's get out'a here
    if (! $matches) {
        return $excerpt;
    }

    // We have to count the words
    $excerpt = explode(' ', $matches[1]);

    // if they're 30 or less, we don't need an ellipsis
    if (count($excerpt) <= 30) {
        return implode(' ', $excerpt);
    } else {
        $out = implode(' ', array_slice($excerpt, 0, 30)).'&hellip;</p>';
        if (! is_front_page()) {
            $out .= '<p class="read-more"><a href="'.get_permalink().'">Read more &raquo;</a></p>';
        }
        return $out;
    }
});

// 5. Digital edition `posts_per_page` should be 48
add_action('pre_get_posts', function ($query) {
    if ($query->is_main_query() && $query->get('post_type') == 'digital_edition' && ! is_admin()) {
        $query->set('posts_per_page', '48');
    }
});

// 6. Remove hero image article from main query
add_action('pre_get_posts', function ($query) {
    if ($query->is_main_query() && $query->is_category() && ! is_admin()) {
        $category_id = $query->get_queried_object_id();

        $post = HamodiaCategory::make($category_id)->getPost();

        if ($post) {
            $query->set('post__not_in', [$post->ID]);
        }
    }
});
