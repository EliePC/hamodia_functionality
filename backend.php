<?php
/*
1.  Enqueue scripts
2.  Define the custom boxes for regular posts
3.  When the post is saved, save our custom data
4.  Define the custom boxes for digital edition
5.  When the `digital edition` post is saved, save our custom data
6.  Remove article from homepage placement upon deletion
7.  Add favicon to admin head
8.  Add HR button to TinyMCE
9.  Add TinyMCE paste filter
10. Turn the tags metabox into a checklist
11. Register the quality control class
12. Register the story updates class
*/

include_once('admin_restrictions.php');
include_once('quality-control.php');
include_once('quick_publish.php');
include_once('tags.php');

// 1. Enqueue scripts
add_action('admin_enqueue_scripts', function () {
    // All this crap for the jQuery UI Datepicker:
    wp_enqueue_style('jquery-ui-smoothness', plugin_dir_url(__FILE__).'/smoothness/jquery-ui-1.8.17.custom.css');
    wp_enqueue_script('hamodia_post_meta_datepicker', get_home_url().'/wp-includes/js/jquery/ui/datepicker.min.js', 'jquery-ui-core', '1.11.2', true);
    wp_enqueue_script('hamodia_post_meta', plugin_dir_url(__FILE__) . '/main.js', 'hamodia_post_meta_datepicker', '0.0.2', true);
});

// 2. Define the custom boxes for regular posts
add_action('add_meta_boxes', function () {
    // Meta box for State, Source, Author & homepage
    foreach (['post', 'hamodia_feature'] as $post_type) {
        add_meta_box('hamodia_post_meta', 'Meta information', function ($post) {
            include __DIR__.'/views/meta-boxes/meta-information.php';
        }, $post_type);
    }
    foreach (['post', 'sponsored_content'] as $post_type) {
        add_meta_box('hamodia_post_meta', 'Meta information', function ($post) {
            include __DIR__.'/views/meta-boxes/meta-information.php';
        }, $post_type);
    }

    // Meta box for Print Edition
    foreach (['sponsored_content', 'digital_edition', 'letter' ] as $post_type) {
        add_meta_box('hamodia_post_meta_print_edition', 'Print Edition', function ($post) {
            wp_nonce_field(plugin_basename(__FILE__), 'hamodia_post_meta_nonce');

            include __DIR__.'/views/meta-boxes/print-edition.php';
        }, $post_type);
    }

    HomepagePlacement::make()->register();
});

// 3. When the post is saved, save our custom data
add_action('save_post', function ($id) {
    // We shouldn't do this on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Verify nonce
    if (! isset($_POST['hamodia_post_meta_nonce']) || ! wp_verify_nonce($_POST['hamodia_post_meta_nonce'], plugin_basename(__FILE__))) {
        return;
    }
    // Check permissions
    if (! current_user_can('edit_post', $id)) {
        return;
    }

    // OK, we're authenticated. Let's go:
    foreach (['state', 'source', 'author', 'date', 'page'] as $key) {
        $key = 'hamodia_post_meta_' . $key;

        update_post_meta(
            $id,
            '_' . $key,
            esc_attr($_POST[$key])
        );
    }

    HomepagePlacement::make()->update($id, $_POST['hamodia_home_page_article']);
});

// 4. Define the custom boxes for digital edition
add_action('add_meta_boxes', function () {
    add_meta_box('hamodia_epaperflip', 'ePaperFlip', function ($post) {
        wp_nonce_field(plugin_basename(__FILE__), 'hamodia_epaperflipid_nonce');

        $value = get_post_meta($post->ID, '_hamodia_epaperflipid', true);

        include __DIR__.'/views/meta-boxes/epaperflip.php';
    }, 'digital_edition');
});

// 5. When the `digital edition` post is saved, save our custom data
add_action('save_post', function ($post_id) {
    // We shouldn't do this on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Verify nonce
    if (! isset($_POST['hamodia_epaperflipid_nonce']) || ! wp_verify_nonce($_POST['hamodia_epaperflipid_nonce'], plugin_basename(__FILE__))) {
        return;
    }
    // Check permissions
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // OK, we're authenticated. Let's go:
    update_post_meta($post_id, '_hamodia_epaperflipid', esc_attr($_POST['hamodia_epaperflipid']));
});

// 6. Remove article from homepage placement upon deletion
HomepagePlacement::make()->registerOnDelete();

// 7. Add favicon to admin head
add_action('admin_head', function () {
    $favicon = get_bloginfo('template_url') . '/favicon.png';
    echo '<link rel="shortcut icon" href="' . $favicon . '" />';
});

// 8. Add HR button to TinyMCE
add_filter("mce_buttons_2", function ($buttons) {
    $buttons[] = 'hr';

    return $buttons;
});

// 9. Add TinyMCE paste filter
add_filter('tiny_mce_before_init', function ($in) {
    $in['paste_preprocess'] = file_get_contents(__DIR__.'/TinyMCE-paste-preprocess.js');

    return $in;
});

// 10. Turn the tags metabox into a checklist
HamodiaTags::register();

// 11. Register the quality control class
HamodiaQualityControl::register();

// 12. Register the story updates class
HamodiaStoryUpdates::register();
