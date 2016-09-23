<?php

class Hamodia_homepage
{
    public static function promotional()
    {
        if ($page = Hamodia_homepage_model::get_promotional()) {
            static::render_promotional($page);
        }
    }

    public static function main_articles()
    {
        $articles = Hamodia_homepage_model::get_main_articles();

        static::render_main_articles($articles);
    }

    public static function category($category, $articles = 4, $hero = true)
    {
        static::display_heading($category);

        if ($hero) {
            static::display_hero($category);
        }

        static::display_posts($category, $articles);

        static::display_more_in_category_link($category);
    }

    public static function newsletter()
    {
        ?>
		<article id="homepage-newsletter">
			<h3>Choose your news!</h3>
			<p>
				Sign up for <b>HAMODIA'S</b><br>
				email newsletter today*
			</p>
			<form action="<?= get_site_url() ?>/standalone/newsletter.php" target="_blank" method="get">
				<input type="email" placeholder="Your Email Here" name="email" required>
				<button type="submit">Go</button>
			</form>
			<footer>*You can select which emails you'd like to receive.</footer>
		</article>
		<?php

    }

    protected static function display_heading($category)
    {
        $category = Hamodia_homepage_model::get_category($category);

        static::render_heading($category);
    }

    protected static function display_hero($category)
    {
        $article = Hamodia_homepage_model::get_category_hero($category);

        static::render_hero($article, 'hero-medium');
    }

    protected static function display_posts($category, $articles)
    {
        $query = Hamodia_homepage_model::get_category_articles($category, $articles);

        while ($query->have_posts()) {
            $query->the_post();

            static::render_article();
        }
    }

    protected static function display_more_in_category_link($category)
    {
        $category = Hamodia_homepage_model::get_category($category);

        ?>
        <a href="<?= get_category_link($category) ?>" class="home-category-more-link">
            More in <?= $category->name ?> »
        </a>
        <?php
    }

    protected static function render_promotional(WP_Post $page)
    {
        ?>
        <div class="homepage-promotional">
            <?= $page->post_content ?>
        </div>
        <?php
    }

    protected static function render_main_articles($articles)
    {
        ?>
		<div class="row home-row-main-primary">
			<?php static::render_hero(array_shift($articles), 'hero-big'); ?>
		</div>
		<div class="row home-row-main-secondary">
			<?php foreach ($articles as $article): ?>
				<div class="four columns">
					<?php static::render_hero($article, 'hero-small'); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
    }

    protected static function render_heading($category)
    {
        ?>
		<h2 class="home-category-heading">
			<a href="<?= get_category_link($category) ?>" class="home-category-link">
				<?= static::get_category_title($category) ?>
			</a>
		</h2>
		<?php
    }

    protected static function get_category_title($category)
    {
        return $category->name == 'Technology' ? 'Health, Science & Technology' : $category->name;
    }

    protected static function render_hero($article, $class = null)
    {
        $post = $article['post'];
        $img  = $article['img'];

        $attributes = static::get_hero_attributes($post, $img, $class);

        ?>
		<a <?= $attributes ?>>
			<h2 class="hero-title"><?= get_the_title($post) ?></h2>
			<?= $img['img'] ?>
		</a>
		<?php
    }

    protected static function get_hero_attributes($post, $img, $class)
    {
        if (has_post_format('gallery', $post)) {
            $class .= ' gallery-format';
        }

        return static::attributes([
            'href'  => get_permalink($post),
            'style' => 'padding-bottom: 55.921%',
            'class' => 'hero '.$class,
        ]);
    }

    protected static function render_article()
    {
        ?>
		<a class="home-article-link" href="<?php the_permalink(); ?>">
			<span class="home-article-title"><?php the_title(); ?></span>
		</a>
		<?php
    }

    protected static function attributes(array $attributes)
    {
        $output = [];

        foreach ($attributes as $key => $value) {
            $output[] = $key.'="'.esc_attr($value).'"';
        }

        return implode(' ', $output);
    }
}




class Hamodia_homepage_model
{
    protected static $used = [];

    protected static $promotional_title = 'Homepage Promotional';

    protected static $ids = [
        'business'   => 15,
        'community'  => 49,
        'editorial'  => 18,
        'israel'     => 14,
        'markets'    => 4524,
        'national'   => 48,
        'opinion'    => 17,
        'politics'   => 7,
        'regional'   => 29,
        'technology' => 629,
        'world'      => 6,
        'sponsored_content' => 9,
        'classifieds' => 10,
    ];

    public static function get_promotional()
    {
        $page = get_page_by_path(static::$promotional_title);

        return $page && $page->post_status == 'publish' ? $page : null;
    }

    public static function get_main_articles($count = 4)
    {
        $articles = [];

        foreach (HomepagePlacement::make()->get() as $id) {
            $post = get_post($id);

            $articles[] = [
                'post' => $post,
                'img'  => get_the_thumbnail('hero-image', 'hero-image', true, $post),
            ];
        }

        if (count($articles) != $count) {
            foreach ($articles as $article) {
                static::mark_used($article['post']);
            }

            $articles = array_merge($articles, static::get_posts_with_image(
                null, 'hero-image', 'hero-image', $count - count($articles)
            ));
        }

        foreach ($articles as $article) {
            static::mark_used($article['post']);
        }

        return $articles;
    }

    public static function get_category($category)
    {
        return get_category(static::$ids[$category]);
    }

    public static function get_category_hero($category)
    {
        if ($sticky = static::get_category_sticky($category)) {
            $article = [
                'post' => $sticky,
                'img'  => get_the_thumbnail('hero-image', 'hero-image', true, $sticky),
            ];
        } else {
            $article = static::get_first_post_with_image(
                $category, 'hero-image', 'hero-image'
            );
        }

        static::mark_used($article['post']);

        return $article;
    }

    public static function get_category_articles($category, $limit)
    {
        $query = static::query($category, $limit);

        static::mark_used($query);

        return $query;
    }

    protected static function get_category_sticky($category)
    {
        $stickies = (array) get_option('sticky_posts');

        if (! $stickies) {
            return null;
        }

        $query = static::query($category, 1, ['post__in' => $stickies]);

        return $query->post_count ? $query->posts[0] : null;
    }

    protected static function get_first_post_with_image($category, $size, $class)
    {
        $posts = static::get_posts_with_image($category, $size, $class, 1);

        if (count($posts)) {
            return $posts[0];
        }
    }

    protected static function get_posts_with_image($category, $size, $class, $count = 1)
    {
        $found = [];

        list($offset, $limit) = [0, 5];

        do {
            $query = static::query($category, $limit, compact('offset'));

            while ($query->have_posts()) {
                $query->the_post();

                if ($img = get_the_thumbnail($size, $class, true)) {
                    $post = $query->posts[$query->current_post];

                    $found[] = compact('img', 'post');
                }

                if (count($found) == $count) {
                    return $found;
                }
            }

            $offset += $limit;
        } while (count($query->posts));

        return $found;
    }

    protected static function query($category, $limit = 3, $options = [])
    {
        return new WP_Query($options + [
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'cat'            => $category ? static::$ids[$category] : null,
            'post__not_in'   => static::$used,
        ]);
    }

    protected static function mark_used($query)
    {
        if (! $query instanceof WP_Query) {
            static::$used[] = $query->ID;

            return;
        }

        foreach ($query->posts as $post) {
            static::$used[] = $post->ID;
        }
    }
}
