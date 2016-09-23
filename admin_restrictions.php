<?php

// Hide un-editable pages
add_action('pre_get_posts', function ($query) {
    // If the current user is me
    if (get_current_user_id() == 1) {
        return;
    }

    if (! $query->is_main_query()) {
        return;
    }

    $query->set('post__not_in', [
        '175',    // Archives
        '96',     // Briefs
        '206',    // Currency
        '174',    // Home
        '224',    // Letters
        '920',    // Adverts
        '207',    // Stocks
        '203',    // Weather
        '248',    // Advertise
        '3723',   // About
        '3722',   // Contact
        '94',     // Subscribe
        '2198',   // classifieds
        '1029'    // Sponsored Content
    ]);
});

add_action('admin_menu', function () {
    // If the current user is me
    if (get_current_user_id() == 1) {
        return;
    }

    // Remove menu items
    remove_menu_page('users.php');
    remove_menu_page('plugins.php');
    remove_menu_page('tools.php');
    remove_menu_page('themes.php');
    remove_menu_page('link-manager.php');
    remove_menu_page('options-general.php');

    // Remove dashboard widgets
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
    remove_meta_box('dashboard_plugins',        'dashboard', 'core');
    remove_meta_box('dashboard_primary',        'dashboard', 'core');
    remove_meta_box('dashboard_secondary',      'dashboard', 'core');
});
