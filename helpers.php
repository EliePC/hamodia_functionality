<?php

class Hamodia_helpers
{
    public static function get_category_info($post_id = null, $get_link = true)
    {
        global $post;

        if ($post_id === null) {
            $post_id = $post->ID;
        }

        $out = [];

        $categories = get_the_category($post_id);

        $out['name'] = $categories[0]->name;

        if ($get_link) {
            $out['link'] = get_category_link($categories[0]->cat_ID);
        }

        return $out;
    }
}
