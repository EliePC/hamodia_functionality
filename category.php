<?php

class HamodiaCategory
{
    /**
     * The current category.
     *
     * @var int
     */
    protected $category;

    /**
     * The post to use as the hero.
     *
     * @var \WP_Post|false
     */
    protected $post;

    /**
     * Holds the instances created, keyed by the category id.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * Constructor.
     *
     * @param int  $category
     */
    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     * Create a new instance.
     *
     * @param  int  $category
     * @return static
     */
    public static function make($category)
    {
        $instance = new static($category);

        static::$instances[$category] = $instance;

        return $instance;
    }

    /**
     * Show the hero image.
     *
     * @return int|null
     */
    public function hero()
    {
        $article = $this->getArticle();

        if (! $article) {
            return;
        }

        $this->renderHero($article);

        return $article['post']->ID;
    }

    /**
     * Get the post being used for the hero image.
     *
     * @return \WP_Post|null
     */
    public function getPost()
    {
        return $this->getSticky();
    }

    /**
     * Get the article to display in the hero box.
     *
     * @return array|null
     */
    protected function getArticle()
    {
        if ($post = $this->getSticky()) {
            return [
                'post' => $post,
                'img'  => get_the_thumbnail('hero-image', 'hero-image', true, $post),
            ];
        }
    }

    /**
     * Get the sticky post.
     *
     * @return \WP_Post|null
     */
    protected function getSticky()
    {
        if ($this->post === false) {
            return;
        }

        $stickies = (array) get_option('sticky_posts');

        if (! $stickies) {
            $this->post = false;

            return;
        }

        $query = new WP_Query([
            'posts_per_page' => 1,
            'cat'            => $this->category,
            'post__in'       => $stickies,
        ]);

        if ($query->post_count) {
            return $this->post = $query->posts[0];
        } else {
            $this->post = false;
        }
    }

    /**
     * Render the hero image HTML.
     *
     * @param  array  $article
     * @return void
     */
    protected function renderHero($article)
    {
        $post = $article['post'];
        $img  = $article['img'];

        $attributes = $this->getHeroAttributes($post, $img, 'hero-big'); ?>

        <a <?= $attributes ?>>
            <h2 class="hero-title"><?= get_the_title($post) ?></h2>
            <?= $img['img'] ?>
        </a>
        <?php
    }

    /**
     * Get the hero image HTML attributes.
     *
     * @param  \WP_Post $post
     * @param  array  $img
     * @param  string  $class
     * @return string
     */
    protected function getHeroAttributes(WP_Post $post, array $img, $class)
    {
        if (has_post_format('gallery', $post)) {
            $class .= ' gallery-format';
        }

        return $this->attributes([
            'href'  => get_permalink($post),
            'style' => $this->getHeroStyle($img),
            'class' => 'hero '.$class,
        ]);
    }

    /**
     * Get the styles for the hero image.
     *
     * @param  array  $image
     * @return string
     */
    protected function getHeroStyle(array $image)
    {
        $height = $image['props']['height'];

        $width = $image['props']['width'];

        $percentage = $height / $width * 400;

        return 'padding-bottom: '.$percentage.'%';
    }

    /**
     * Compile attributes into an HTML string.
     *
     * @param  array  $attributes
     * @return string
     */
    protected function attributes(array $attributes)
    {
        $output = [];

        foreach ($attributes as $key => $value) {
            $output[] = $key.'="'.esc_attr($value).'"';
        }

        return implode(' ', $output);
    }
}
