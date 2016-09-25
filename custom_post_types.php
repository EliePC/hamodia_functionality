<?php
/*
1. Features
2. Letters
4. Digtal Edition
*/

// 1. Features
add_action('after_setup_theme', function () {
    register_post_type('hamodia_feature', [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'menu_position'      => 4,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'features', 'with_front' => false],
        'supports'           => ['title', 'editor'],
        'labels'             => [
            'name'               => 'Features',
            'singular_name'      => 'Feature',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Feature',
            'edit_item'          => 'Edit Feature',
            'new_item'           => 'New Feature',
            'all_items'          => 'All Features',
            'view_item'          => 'View Feature',
            'search_items'       => 'Search Features',
            'not_found'          => 'No Features found',
            'not_found_in_trash' => 'No Features found in Trash',
            'menu_name'          => 'Features',
        ],
    ]);

    register_taxonomy('feature_category', 'hamodia_feature', [
        'hierarchical' => true,
        'show_ui'      => true,
        'query_var'    => true,
        'rewrite'      => ['slug' => 'feature-category'],
        'labels'       => [
            'name'              => 'Categories',
            'singular_name'     => 'Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories',
        ],
    ]);
});

// 2. Letters
add_action('after_setup_theme', function () {
    register_post_type('letter', [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'menu_position'      => 4,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'letters', 'with_front' => false],
        'supports'           => ['title', 'editor'],
        'labels'             => [
            'name'               => 'Letters',
            'singular_name'      => 'Feature',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Letter',
            'edit_item'          => 'Edit Letter',
            'new_item'           => 'New Letter',
            'all_items'          => 'All Letters',
            'view_item'          => 'View Letter',
            'search_items'       => 'Search Letters',
            'not_found'          => 'No Letters found',
            'not_found_in_trash' => 'No Letters found in Trash',
            'menu_name'          => 'Letters',
        ],
    ]);
});

// 3. Sponsored Content
add_action('after_setup_theme', function () {
    register_post_type('sponsored_content', [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'menu_position'      => 4,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'sponsored_content'],
        'supports'           => ['title', 'editor'],
        'labels'             => [
            'name'               => 'Sponsored Content',
            'singular_name'      => 'Sponsored Content',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Sponsored Content',
            'edit_item'          => 'Edit Sponsored Content',
            'new_item'           => 'New Sponsored Content',
            'all_items'          => 'All Sponsored Content',
            'view_item'          => 'View Sponsored Content',
            'search_items'       => 'Search Sponsored Content',
            'not_found'          => 'No Sponsored Content found',
            'not_found_in_trash' => 'No Sponsored Content found in Trash',
            'menu_name'          => 'Sponsored Content',
    ],
  ]);
});

//3.- Classifieds
 add_action('after_setup_theme', function () {
    register_post_type('classifieds', [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,  //true if you want to put into the menu better false
        'query_var'          => true,
        'menu_position'      => 4,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'classified-4'],
        'supports'           => ['title', 'editor'],
        'labels'             => [
            'name'               => 'Classifieds',
            'singular_name'      => 'Classifieds',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Classified',
            'edit_item'          => 'Edit Classifieds',
            'new_item'           => 'New Classifieds',
            'all_items'          => 'All Classifieds',
            'view_item'          => 'View Classifieds',
            'search_items'       => 'Search Classifieds',
            'not_found'          => 'No Classifieds found',
            'not_found_in_trash' => 'No Classifieds found in Trash',
            'menu_name'          => 'Classifieds',
    ],
  ]);
});


// 4. Digtal Edition
add_action('after_setup_theme', function () {
    register_post_type('digital_edition', [
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'menu_position'      => 4,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'digital-edition', 'with_front' => false],
        'supports'           => ['title', 'thumbnail'],
        'labels'             => [
            'name'               => 'Digital Editions',
            'singular_name'      => 'Digital Edition',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Digital Edition',
            'edit_item'          => 'Edit Digital Edition',
            'new_item'           => 'New Digital Edition',
            'all_items'          => 'All Digital Editions',
            'view_item'          => 'View Digital Edition',
            'search_items'       => 'Search Digital Editions',
            'not_found'          => 'No Digital Editions found',
            'not_found_in_trash' => 'No Digital Editions found in Trash',
            'menu_name'          => 'Digital Edition',
        ],
    ]);

    add_theme_support('post-thumbnails', ['digital_edition']);
    add_image_size('paper-front-page', 130, 180, true);
});
