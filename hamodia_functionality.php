<?php
/*
Plugin Name: Hamodia Functionality
Plugin URI: http://josephsilber.com
Description: Special functionality plugin built specifically for Hamodia.com's needs.
Author: Joseph Silber
Version: 0.1
Author URI: http://josephsilber.com
*/

include_once('login.php');
include_once('custom_post_types.php');
include_once('homepage_placement.php');
include_once('story-updates.php');

add_image_size('hero-image', 760, 425, true);

if (is_admin()) {
    include_once('backend.php');
} else {
    include_once('frontend.php');
}

register_activation_hook(__FILE__, function () {
    // Add edit pages capability to "editor"
    $role_object = get_role('editor');
    $role_object->add_cap('edit_pages');
});

register_deactivation_hook(__FILE__, function () {
    // Remove pages capability from "editor"
    $role_object = get_role('editor');
    $role_object->remove_cap('edit_pages');
});
