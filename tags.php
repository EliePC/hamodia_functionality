<?php

class HamodiaTags
{
    /**
     * The current taxonomies.
     *
     * @var array
     */
    protected $taxonomies;

    /**
     * The singleton instance.
     *
     * @var \HamodiaTags
     */
    protected static $instance;

    /**
     * Constructor.
     *
     * @param array  $taxonomies
     */
    public function __construct(array $taxonomies)
    {
        $this->taxonomies = $taxonomies;
    }

    /**
     * Make a new instance of this class.
     *
     * @return static
     */
    public static function make()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($GLOBALS['wp_taxonomies']);
        }

        return static::$instance;
    }

    /**
     * Register the appropriate Wordpress hooks.
     *
     * @return void
     */
    public static function register()
    {
        add_action('admin_menu', function () {
            static::make()->removeTagsMetaBox();
        });

        add_action('add_meta_boxes', function () {
            static::make()->addCustomMetaBox();
        });

        add_action('init', function () {
            if ( ! isset($_POST['tax_input']['post_tag'])) {
                return;
            }

            $flattened = static::make()->flatten($_POST['tax_input']['post_tag']);

            $_POST['tax_input']['post_tag'] = $flattened;
        });
    }

    /**
     * Remove the default tags metabox.
     *
     * @return void
     */
    public function removeTagsMetaBox()
    {
        remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
    }

    /**
     * Add our custom metabox.
     *
     * @return void
     */
    protected function addCustomMetaBox()
    {
        add_meta_box(
            $id       = 'post_tag-checklist',
            $title    = 'Tags',
            $callback = [$this, 'renderCustomMetaBox'],
            $screen   = 'post',
            $context  = 'side',
            $priority = 'low'
        );
    }

    /**
     * Render our custom metabox.
     *
     * @param  \WP_Post  $post
     * @return void
     */
    public function renderCustomMetaBox(WP_Post $post)
    {
        ?>
        <div id="taxonomy-post_tag" class="categorydiv">
            <input type="search" placeholder="Filter tag list" style="width: 100%">
            <div id="post_tag-all" class="tabs-panel">
                <ul id="post-tag-checklist" class="list:post_tag form-no-clear">
                    <?php wp_terms_checklist($post->ID, ['taxonomy' => 'post_tag']); ?>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Flatten the given tags into a comma delimited string.
     *
     * @param  array|string  $input
     * @return string
     */
    protected function flatten($input)
    {
        if (is_string($input)) return $input;

        return implode(', ', $this->getTermNamesById($input));
    }

    /**
     * Gets the names of the terms by the given IDs.
     *
     * @param  array  $ids
     * @return array
     */
    protected function getTermNamesById(array $ids)
    {
        $terms = get_terms('post_tag', [
            'include' => $ids,
            'hide_empty' => false,
        ]);

        return array_map(function ($term) {
            return $term->name;
        }, $terms);
    }
}
