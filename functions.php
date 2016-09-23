<?php
/*
1.  Get the info for the print edition
2.  Get author info
3.  Get opinion disclaimer
4.  Build main nav menu
5.  Get attachment ID for an image's link
6.  Get the thumbnail image
8.  Get the HTML title
9.  Render the Stock Ticker
10. Get current URL
11. Get breadcrumbs
12. Get meta information
13. Get article date
14. Format date
15. Tweak the author display name
16. Check if related articles should be displayed
*/

// 1. Get the info for the print edition
if (! function_exists('hamodia_post_meta')) {
    function hamodia_post_meta($return = false)
    {
        /*
            Sample output:
            This article appeared in print in today's edition of Hamodia.
            This article appeared in print on page 5 of today's edition of Hamodia.
            This article appeared in print on page 5 of the December 25 edition of Hamodia.
        */

        global $post;

        if (! is_single()) {
            return;
        }

        $prefix = '_hamodia_post_meta_';

        $date        = get_post_meta($post->ID, $prefix . 'date',    true);
        $page        = get_post_meta($post->ID, $prefix . 'page',    true);

        // If there's no valid date, there's nothing for us to do here
        if (! $date || ! preg_match("~^\d{1,2}/\d{1,2}/\d{4}$~", $date)) {
            return;
        }

        $date = strtotime($date);

        // We don't want to post info about tomorrow's paper, do we?
        if ($date > time()) {
            return;
        }

        $is_today = strtotime('0:00') == $date;

        // Construct HTML strings
        $page_info = ($page ? 'on page ' . $page . ' of ' : 'in ');

        $date_info = $is_today ? 'today\'s' : 'the ' . date('F jS, Y', $date);
        $date_info = '<time datetime="' . date('c', $date) .'">' . $date_info . '</time>';

        $link = '<a href="' . get_page_link(94) . '" class="subscribe-page-link">' . $page_info . $date_info . ' edition of Hamodia</a>';

        $html  = '<aside class="print-edition-info">This article appeared in print ' . $link . '.</aside>';

        if ($return) {
            return $html;
        }

        echo $html;
    }
}

// 2. Get author info
if (! function_exists('hamodia_get_author')) {
    function hamodia_get_author($return = false)
    {
        global $post;

        if (! is_single()) {
            return;
        }

        $prefix = '_hamodia_post_meta_';

        $author = get_post_meta($post->ID, $prefix . 'author', true);

        if (! $author) {
            return;
        }

        $html  = '<p class="byline author vcard">By ' . $author . '</p>';

        if ($return) {
            return $html;
        }

        echo $html;
    }
}

// 3. Get opinion disclaimer
if (! function_exists('hamodia_opinion_disclaimer')) {
    function hamodia_opinion_disclaimer($return = false)
    {
        if (! is_single()) {
            return;
        }

        if (has_category('opinion')) {
            $html  = '<aside class="opinion-disclaimer">The opinions expressed on this page are those of the individual authors and do not necessarily reflect the opinions of Hamodia.</aside>';
        } elseif (get_post_type() == 'letter') {
            $html  = '<aside class="opinion-disclaimer">Hamodia welcomes letters to the editor but can only print submissions that come with a name, address and phone number. Names will be withheld upon request. Submissions can be sent to Hamodia via regular mail, or via e-mail to <a href="mailto:letters@hamodia.com">letters@hamodia.com</a>. We reserve the right to edit letters.</aside>';
        } else {
            return;
        }


        if ($return) {
            return $html;
        }

        echo $html;
    }
}

// 4. Build main nav menu
if (! function_exists('hamodia_main_nav')) {
    function hamodia_main_nav()
    {
        $first_post_on_page = ! empty($GLOBALS['wp_the_query']->posts) ? $GLOBALS['wp_the_query']->posts[0] : '';
        $is_feature = get_post_type($first_post_on_page) == 'hamodia_feature';
        $is_letter = get_post_type() == 'letter';
        $is_classifieds = get_post_type() == 'classifieds';
        $is_sponsored_content = get_post_type() == 'sponsored_content';

        $news_categories = [
            'Business',
            'Technology',
            'World',
            'Regional',
            'Israel',
            'Community',
            'National',
            'Politics',
            'Op-Ed',
         //   'Classified',
        //    'Sponsored Content'
        ];
        ?>

        <ul>
            <?php foreach ($news_categories as $cat): ?>
                <?php hamodia_main_nav_category_helper($cat, $first_post_on_page); ?>
            <?php endforeach; ?>

            <li class="<?= $is_feature ? 'current' : '' ?>">
                <a href="<?= get_post_type_archive_link('hamodia_feature') ?>">Features</a>
            </li>
            <li class="<?= $is_letter ? 'current' : '' ?>">
                <a href="<?= get_post_type_archive_link('letter') ?>">Letters</a>
            </li>
            <li class="<?= $is_classifieds ? 'current' : '' ?>">
                <a href="<?= get_post_type_archive_link('classifieds') ?>">Classifieds</a>
            </li>
            <li class="<?= $is_sponsored_content ? 'current' : '' ?>">
                <a href="<?= get_post_type_archive_link('sponsored_content') ?>">Sponsored Content</a>
            </li>
        </ul>
       <?php
    }
}

function hamodia_main_nav_category_helper($cat, $first_post_on_page)
{
    $is_current = (is_category() || is_single()) && has_category($cat, $first_post_on_page);
    $category_link = get_category_link(get_cat_ID($cat));

    if ($cat === 'Technology') {
        $cat = 'Tech';
    }

    ?>
    <li class="<?= $is_current ? 'current' : '' ?>">
        <a href="<?= $category_link ?>"><?= $cat ?></a>
    </li>
    <?php
}

// 5. Get attachment ID from image link
function get_image_id_from_link($link)
{
    global $wpdb;

    // If there's a size at the end (e.g. -600x300.jpg), remove it
    $link = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $link);

    if (! $link) {
        return false;
    }

    // Now get the attachment ID from the database
    return $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE BINARY guid='$link'");
}

// 6. Get the thumbnail image
if (! function_exists('get_the_thumbnail')) {
    function get_the_thumbnail($size = 'thumbnail', $class = 'excerpt-thumbnail', $details = null, $post = null)
    {
        return HamodiaThumbnail::make($post ?: $GLOBALS['post'])->get($size, $class, $details);
    }
}

// 8. Get the HTML title
if (! function_exists('hamodia_page_title')) {
    function hamodia_page_title()
    {
        // TODO: Remove asterisks
        wp_title('');
    }
}

// 9. Render the Stock Ticker
if (! function_exists('hamodia_stock_ticker')) {
    function hamodia_stock_ticker()
    {
        if (! is_front_page() && ! is_category('Markets')) {
            return;
        }

        $page_link = get_page_link('207');

        $stocks = Hamodia_APIs::get_stocks_info([
            '^DJI',
            '^IXIC',
            '^GSPC',
            'AAPL',
            'MSFT',
            'AMZN',
            'T',
            'S',
            'VZ',
        ]);

        ?>
        <a href="<?= $page_link ?>" class="row stock-ticker" title="Click for more stocks">
            <ul>
                <?php foreach ($stocks as $stock): ?>
                <li>
                    <span class="symbol"><?= $stock['symbol'] ?></span>
                    <span class="price"><?= number_format($stock['price'], 2) ?></span>
                    <span class="change <?= substr($stock['change_points'], 0, 1) == '+' ? 'up' : 'down' ?>">
                        <?= $stock['change_points'] ?> (<?= $stock['change_percent'] ?>)
                    </span>
                <?php endforeach; // Not closing the `li`s, so that there's no space between them ?>
            </ul>
        </a>

        <?php
    }
}

// 10. Get current URL
if (! function_exists('get_current_url')) {
    function get_current_url()
    {
        $pageURL  = (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"]) == "on" ? 'https' : 'http';
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"];
        $pageURL .= $_SERVER["SERVER_PORT"] != "80" ? ":".$_SERVER["SERVER_PORT"] : null;
        $pageURL .= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return $pageURL;
    }
}

// 11. Get breadcrumbs
if (! function_exists('get_breadcrumbs')) {
    function get_breadcrumbs()
    {
        if (! function_exists('yoast_breadcrumb')) {
            return;
        }

        if (isset($_GET['letters'])) {
            return;
        }

        yoast_breadcrumb('<div id="breadcrumbs" class="row">', '</div>');
    }
}

// 12. Get meta information
if (! function_exists('hamodia_entry_meta')) {
    function hamodia_entry_meta($echo = true)
    {
        $html = hamodia_get_entry_date();

        function_exists('hamodia_get_author') && $html .= hamodia_get_author();

        if (! $echo) {
            return $html;
        }

        echo $html;
    }
}

// 13. Get article date
if (! function_exists('hamodia_get_entry_date')) {
    function hamodia_get_entry_date($post_id = null)
    {
        global $post;

        if ($post_id === null) {
            $post_id = $post->ID;
        }

        $html = hamodia_format_date(get_post_time('U', false, $post_id));

        if (HamodiaStoryUpdates::has($post_id)) {
            $timestamp = HamodiaStoryUpdates::latestTimestamp($post_id);

            $html .= '<time class="updated" datetime="'.$timestamp->format('c').'">'.
                'Updated '.$timestamp->format('l, F j, Y \a\t g:i a').
            '</time>';
        }

        return '<div class="post-time-wrapper">'.$html.'</div>';
    }
}

// 14. Format date
if (! function_exists('hamodia_format_date')) {
    function hamodia_format_date($timestamp)
    {
        $hebrew_date = Hamodia_APIs::get_hebrew_date($timestamp);
        $hebrew_date = " | <span class=\"hebrew-date\">$hebrew_date</span>";

        $html = '<time class="created" datetime="'. date('c', $timestamp) .'" pubdate>' .
                    date('l, F j, Y \a\t g:i a', $timestamp) .
                    $hebrew_date .
                '</time>';

        return $html;
    }
}

// 15. Tweak the author display name
add_filter('the_author', function () {
    global $post;

    $prefix = '_hamodia_post_meta_';

    $author = get_post_meta($post->ID, $prefix.'author', true);

    return $author ?: 'Hamodia';
});

// 16. Check if related articles should be displayed
if (! function_exists('should_display_related_posts')) {
    function should_display_related_posts()
    {
        global $post;

        if (in_array(get_post_type(), ['hamodia_feature', 'letter', 'classifieds', 'sponsored_content'])) {
            return false;
        }

        if (has_post_format(['image', 'gallery'])) {
            return false;
        }

        return strpos($post->post_content, '<!--noyarpp-->') === false;
    }
}
