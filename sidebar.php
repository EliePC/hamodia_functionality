<?php

class Hamodia_sidebar
{
    public static function more_in_current_category()
    {
        if (get_post_type() == 'letter') {
            return;
        }

        self::more_in_category_template(
            Hamodia_sidebar_model::more_in_current_category(),
            'More in'
        );
    }

    public static function more_in_categories()
    {
        if (! $more_in_categories = Hamodia_sidebar_model::more_in_categories()) {
            return;
        }

        foreach ($more_in_categories as $data) {
            self::more_in_category_template($data);
        }
    }

    public static function daily_paper()
    {
        $data = Hamodia_sidebar_model::daily_paper();

        ?>
        <div class="sidebar-module" id="daily-paper-module">

            <a class="todays-edition" href="<?= $data['todays_link'] ?>" target="_blank">
                <h3>Hamodia Digital Edition</h3>
                <img src="<?= bloginfo('template_url') . '/images/digital-edition-banner.png' ?>" alt="Digital Edition Banner" width="84" height="115" />
                <p>Browse Today's Print Edition</p>
            </a>

            <a class="more-bar" href="<?= $data['link'] ?>">Browse print archives »</a>

        </div>
        <?php
    }

    public static function newsletter()
    {
        ?>
        <article class="sidebar-module" id="newsletter-sidebar">
            <h3>Want up-to-the-minute news?</h3>
            <p>Sign up for <b>HAMODIA'S</b> Breaking News Emails*</p>
            <form action="<?= get_site_url() ?>/standalone/newsletter.php" target="_blank" method="get">
                <input type="email" placeholder="Your Email Here" name="email" required>
                <button type="submit">Go</button>
            </form>
            <footer>*You can select which emails you'd like to receive.</footer>
        </article>
        <?php

    }

    public static function weather()
    {
        if (! $weather = Hamodia_sidebar_model::weather()) {
            return;
        }

        ?>
        <a class="sidebar-module info-module" id="weather-module" href="<?= $weather['page_link'] ?>">
            <h3>Weather »</h3>
            <img src="<?= get_template_directory_uri() ?>/images/weather/big/<?= $weather['icon_url'] ?>">
            <p class="weather-text">
                <?= sprintf('%s<Br />%s&deg;F %s',
                    'Brooklyn',
                    $weather['temp'],
                    $weather['condition']
                ) ?>
            </p>
            <p class="more-text">See more locations »</p>
        </a>
        <?php
    }

    public static function currency()
    {
        if (! $data = Hamodia_sidebar_model::currency()) {
            return;
        }

        ?>
        <a class="sidebar-module info-module" id="currency-module" href="<?= $data['page_link'] ?>">
            <h3>Currency »</h3>

            <ul>
            <?php foreach ($data['currencies'] as $key): ?>
                <li>
                    <abbr title="<?= $data['currency']['currencies'][$key] ?>">
                        <span class="flag flag-<?= $key ?>"></span>
                        <span class="country"><?= $key ?></span>
                        <span class="rate"><?= $data['currency']['rates'][$key] ?></span>
                    </abbr>
                </li>
            <?php endforeach; ?>
            </ul>

            <p class="more-text">See more currencies »</p>
        </a>
        <?php
    }

    public static function stocks($search_only = false)
    {
        $data = Hamodia_sidebar_model::stocks(! $search_only);

        if ($search_only): ?>
            <div class="sidebar-module info-module" id="stocks-module">

                <h3>Search stocks »</h3>
                <form class="compact" action="<?= $data['page_link'] ?>" method="get">
                    <input type="text" name="symbol" placeholder="Enter symbol" />
                    <input type="submit" value="Go" />
                </form>

                <?php if (! is_page('stocks')): ?>
                    <a class="more-bar" href="<?= $data['page_link'] ?>">See all stocks »</a>
                <?php endif; ?>

            </div>
            <?php return; ?>
        <?php endif; ?>


        <a class="sidebar-module info-module" id="stocks-module" href="<?= $data['page_link'] ?>">
            <h3>Stocks »</h3>

            <ul>
                <?php foreach ($data['stocks'] as $stock): ?>
                    <?php $stock['direction'] = substr($stock['change_points'], 0, 1) == '+' ? 'up' : 'down'; ?>
                    <?php $stock['price'] = number_format($stock['price'], 2); ?>

                    <li>
                        <abbr title="<?= $stock['name'] ?>">
                            <span class="symbol"><?= $stock['symbol'] ?></span>
                            <span class="last"><?= $stock['price'] ?></span>
                            <span class="change <?= $stock['direction'] ?>">
                                <?= $stock['change_points'] ?> (<?= $stock['change_percent'] ?>)
                            </span>
                        </abbr>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="more-text">See more stocks »</p>
        </a>
        <?php
    }

    public static function page_links()
    {
        ?>
        <a target="_blank" class="sidebar-module sidebar-zmanim" href="<?= get_page_link(232) ?>">Hadlakas Neiros</a>
        <a target="_blank" class="sidebar-module sidebar-shiurim" href="<?= get_page_link(234) ?>">Shiurim</a>
        <a target="_blank" class="sidebar-module sidebar-simchos" href="<?= get_page_link(1554) ?>">Simchas</a>
 <!--     <a target="_blank" class="sidebar-module sidebar-classifieds" href="<?= get_page_link(9624) ?>">Classifieds</a>
        <a class="more-bar more-bar-classifieds" href="<?= get_page_link(6161) ?>">Submit your classified ad »</a>  -->
        <?php
    }

    public static function switching_boxes()
    {
        extract(Hamodia_sidebar_model::switching_boxes());

        ?>
        <div class="switching-box sidebar-module">

            <h3>OP-ED</h3>
            <?php self::switching_boxes_template($oped, 'sidebar-op-ed'); ?>

            <h3>Features</h3>
            <?php self::switching_boxes_template($features, 'sidebar-latest-features'); ?>

        </div>
        <?php
    }

    private static function switching_boxes_template($query, $id)
    {
        ?>
        <ul id="<?= $id ?>">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile; ?>
        </ul>
        <?php
    }

    private static function more_in_category_template($data, $title_text = '')
    {
        ?>
        <div class="more-articles">

            <h3><?= $title_text ?>
                <a href="<?= get_term_link($data['term']->slug, $data['taxonomy']) ?>">
                    <?= $data['term']->name ?>
                </a>
            </h3>

            <ul>
                <?php while ($data['query']->have_posts()) : $data['query']->the_post(); ?>
                    <li>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_title(); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php
    }

    public static function news_tip()
    {
        ?>
        <article class="sidebar-module">
            <a id="newstip-sidebar" href="<?= get_site_url() ?>/standalone/news-tip.php" target="_blank">
                <h3>Got a news tip, picture or story to share?</h3>
                <img src="<?= bloginfo('template_url') . '/images/news-tip.svg' ?>" alt="">
                <span>Contribute</span>
            </a>
        </article>
        <?php
    }
}













class Hamodia_sidebar_model
{
    private static $category_ids = [
        'world'      => 6,
        'politics'   => 7,
        'business'   => 15,
        'regional'   => 29,
        'israel'     => 14,
        'opinion'    => 17,
        'editorial'  => 18,
        'national'   => 48,
        'community'  => 49,
        'markets'    => 4524,
        'technology' => 629,
    //    'sponsored_content' => 9,
    ];

    public static function more_in_current_category()
    {
        global $post;

        $data = self::get_term_and_taxonomy();

        if ($data['taxonomy'] == 'category') {
            $post_type  = 'post';
            $taxonomy   = 'category';
            $query_key  = 'category_name';
            $query_val  = $data['term']->slug;
        } else {
            $post_type = ('feature_category');
            $taxonomy  = 'feature_category';
            $query_key = $taxonomy;
            $query_val = $data['term']->slug;
        }
  /*       if ($data['taxonomy'] == 'category') {
             $post_type  = 'post';
             $taxonomy   = 'category';
             $query_key  = 'category_name';
             $query_val  = $data['term']->slug;

         } else {
            $post_type = ('sponsored_content');
            $taxonomy  = 'sponsored_content';
            $query_key = $taxonomy;
            $query_val = $data['term']->slug;
         }
*/


        return [
            'term'     => $data['term'],
            'taxonomy' => $taxonomy,
            'query'    => new WP_Query([
                $query_key       => $query_val,
                'post_type'      => $post_type,
                'post__not_in'   => (array) $post->ID,
                'post_status'    => 'publish',
                'order'          => 'DESC',
                'posts_per_page' => 5.
            ]),
        ];
    }

    public static function daily_paper()
    {
        global $post;

        $out = [
            'link' => get_post_type_archive_link('digital_edition'),
            'todays_link' => '#',
        ];

        // TODO: There's a bug in this query that somehow
        // has the SQL query run with LIMIT set to 50 (you can
        // see the raw SQL by calling `$query->result`).
        // This bug is not critical, since we just use the
        // first post returned, but it's a waste of resources.
        $query = new WP_Query(array(
            'post_type'      => 'digital_edition',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ));

        if ($query->have_posts()) {
            $query->the_post();
            $out['todays_link'] = get_permalink();
        }

        return $out;
    }

    public static function weather()
    {
        $weather = Hamodia_APIs::get_weather_info();

        $out = [];

        $out['page_link'] = get_page_link(get_page_by_title('Weather')->ID);
        $out['temp']      = $weather['current']['temp_f'];
        $out['condition'] = $weather['current']['condition'];
        $out['icon_url']  = $weather['current']['icon'];

        return $out;
    }

    public static function currency()
    {
        $currency_page = get_page_by_title('Currency Exchange Rates');

        if (! $currency_page || is_page($currency_page->ID)) {
            return false;
        }

        return [
            'currency'   => Hamodia_APIs::get_currency_info(),
            'currencies' => array('EUR', 'GBP', 'ILS', 'CHF'),
            'page_link'  => get_page_link($currency_page->ID)
        ];
    }

    public static function stocks($get_stocks = true)
    {
        $out = [
            'page_link' => get_page_link('207')
        ];

        if ($get_stocks) {
            $out['stocks'] = Hamodia_APIs::get_stocks_info([
                '^DJI',
                '^IXIC',
                '^GSPC'
            ]);
        }

        return $out;
    }

    public static function switching_boxes()
    {
        $oped = new WP_Query([
            'post_status'        => 'publish',
            'order'                => 'DESC',
            'posts_per_page'    => 14,
            'cat'                => 16,
        ]);

        $features = new WP_Query([
            'post_type'            => 'hamodia_feature',
            'post_status'        => 'publish',
            'order'                => 'DESC',
            'posts_per_page'    => 14
        ]);

        /* $sponsored_content = new WP_Query([
            'post_type'            => 'sponsored_content',
            'post_status'        => 'publish',
            'order'                => 'DESC',
            'posts_per_page'    => 14
        ]);
*/
        return compact('oped', 'features'/*sponsored_content'*/);
    }

    public static function more_in_categories()
    {
        $data = self::get_term_and_taxonomy();
        $out = [];

        if (! isset($data['taxonomy']) || $data['taxonomy'] == 'category') {
            $top_categories = ['business', 'israel', 'world'];

            if (isset($data['term'])) {
                // Remove the current category from the array
                $top_categories = array_diff($top_categories, [$data['term']->slug]);
            }

            // but we only want the first 2 top categories
            $top_categories = array_slice($top_categories, 0, 2);

            foreach ($top_categories as $category) {
                $out[] = [
                    'taxonomy' => 'category',
                    'term'     => get_term_by('slug', $category, 'category'),
                    'query'    => new WP_Query([
                        'category_name'  => $category,
                        'post_status'    => 'publish',
                        'order'          => 'DESC',
                        'posts_per_page' => 5
                    ]),
                ];
            }

            return $out;
        }
    }

    public static function get_term_and_taxonomy()
    {
        global $post;

        $out = [];
        $post_type = get_post_type();

        switch ($post_type) {
            case 'post':
                $categories = get_the_category();

                $out['taxonomy'] = 'category';
                $out['term']     = $categories[0];
                break;

            case 'hamodia_feature':
                $terms = get_the_terms($post->ID, 'feature_category');

                $out['taxonomy'] = 'feature_category';
                $out['term']     = array_shift($terms);
                break;

        }

        return $out;
    }
}
