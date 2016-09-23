<?php

class HamodiaQualityControl
{
    /**
     * The key used for our metadata.
     *
     * @var string
     */
    protected $key = '_hamodia_quality_control';

    /**
     * The metadata for the current post.
     *
     * @var array
     */
    protected $data;

    /**
     * The key to use in the form input.
     *
     * @var string
     */
    protected static $inputKey = 'hamodia_quality_control';

    /**
     * The list of items that need to be reviewed.
     *
     * @var array
     */
    protected $reviews = [
        'Hashgacha',
        'Editor',
        'Category',
        'Tags',
    ];

    /**
     * The singleton instance.
     *
     * @var \HamodiaTags
     */
    protected static $instance;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        foreach ($this->reviews as $review) {
            $this->data['status'][$review] = false;
        }
    }

    /**
     * Make a new instance of this class.
     *
     * @return static
     */
    public static function make()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
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
        add_action('add_meta_boxes_post', function () {
            static::make()->addCustomMetaBox();
        });

        add_action('save_post', function ($id) {
            if ( ! static::shouldSaveMetadata($id, $_POST)) {
                return;
            }

            $input = (array) $_POST[static::$inputKey];

            static::make()->load($id)->update($input)->commit($id);
        });
    }

    /**
     * Determines whether we should save metadata now.
     *
     * @param  int  $id
     * @param  array  $input
     * @return bool
     */
    protected static function shouldSaveMetadata($id, $input)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if ( ! array_key_exists(static::$inputKey, $input)) {
            return false;
        }

        return current_user_can('edit_post', $id);
    }

    /**
     * Add our custom metabox.
     *
     * @return void
     */
    protected function addCustomMetaBox()
    {
        add_meta_box(
            $id       = 'hamodia-post-review',
            $title    = 'Quality Control',
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
        $this->load($post);

        ?>
        <div class="tabs-panel">
            <ul id="hamodia-review-checklist" class="form-no-clear">
                <?php foreach ($this->data['status'] as $title => $approved): ?>
                    <?php $this->renderCheckbox($title, $approved); ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php

        $this->renderHistory();
    }

    /**
     * Render a checkbox for an individual review item.
     *
     * @param  string  $title
     * @param  bool  $checked
     * @return void
     */
    protected function renderCheckbox($title, $checked = false)
    {
        ?>
        <li>
            <label class="selectit">
                <input value="on"
                       type="checkbox"
                       name="<?= static::$inputKey ?>[<?= $title ?>]"
                       <?= $checked ? 'checked' : null ?>
                >
                <?= $title ?>
            </label>
        </li>
        <?php
    }

    protected function renderHistory()
    {
        if (count($this->data['history']) == 0) {
            return;
        }

        ?>
        <button class="button"
                type="button"
                style="margin-top: 10px"
                onclick="this.classList.add('hidden'); document.getElementById('review-history').classList.remove('hidden');"
        >
            Show audit trail
        </button>

        <ul id="review-history" class="hidden">
            <?php foreach ($this->history() as $entry) : ?>
                <li><?= $this->historyText($entry) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Compile the text for a history entry.
     *
     * @param  array  $entry
     * @return string
     */
    protected function historyText(array $entry)
    {
        if ($entry['approved']) {
            $action = '<b style="color:#59A720">approved</b>';
        } else {
            $action = '<b style="color:#dd3d36">disapproved</b>';
        }

        return "{$entry['user']} {$action} {$entry['review']}";
    }

    /**
     * Load the post's review data.
     *
     * @param  \WP_Post|int  $post
     * @return $this
     */
    protected function load($post)
    {
        $data = $this->fetch($post);

        $this->data = $data ? $this->diff($data) : $this->stub();

        return $this;
    }

    /**
     * Get a stub of review data.
     *
     * @return array
     */
    protected function stub()
    {
        $data = [
            'history' => [],
            'status' => [],
        ];

        foreach ($this->reviews as $review) {
            $data['status'][$review] = false;
        }

        return $data;
    }

    /**
     * Add/remove missing/extra review items.
     *
     * @param  array  $data
     * @return array
     */
    protected function diff($data)
    {
        $existing = array_keys($data['status']);

        // If there are any review keys that were saved in the DB
        // but aren't in our reviews keys, we will remove them
        // here from the existing statuses to properly sync.
        foreach (array_diff($existing, $this->reviews) as $removed) {
            unset($data['status'][$removed]);
        }

        // Conversely, if there are review keys present in our
        // list but not in the saved one, we will add them
        // here. We should always keep them all in sync.
        foreach (array_diff($this->reviews, $existing) as $added) {
            $data['status'][$added] = false;
        }

        return $data;
    }

    /**
     * Update the review data from the given input.
     *
     * @param  array  $input
     * @return $this
     */
    protected function update(array $input)
    {
        foreach ($this->reviews as $key) {
            $approved = array_key_exists($key, $input) && $input[$key] == 'on';

            $approved ? $this->approve($key) : $this->disapprove($key);
        }

        return $this;
    }

    /**
     * Approve the given review key.
     *
     * @param  string  $key
     * @return void
     */
    protected function approve($key)
    {
        $this->set($key, true);
    }

    /**
     * Disapprove the given review key.
     *
     * @param  string  $key
     * @return void
     */
    protected function disapprove($key)
    {
        $this->set($key, false);
    }

    /**
     * Set the given review key.
     *
     * @param string  $key
     * @param bool  $value
     */
    protected function set($key, $value)
    {
        if ($this->data['status'][$key] == $value) {
            return;
        }

        $this->data['status'][$key] = $value;

        $this->data['history'][] = [
            'user'     => $this->userId(),
            'review'   => $key,
            'approved' => $value,
        ];
    }

    /**
     * Fetch the review data from the DB.
     *
     * @param  \WP_Post|int  $id
     * @return array|null
     */
    protected function fetch($post)
    {
        $id = $post instanceof WP_Post ? $post->ID : $post;

        $value = get_post_meta($id, $this->key, true);

        if ($value) {
            return json_decode($value, true);
        }
    }

    /**
     * Save the review data to the DB.
     *
     * @param  int  $id
     * @return void
     */
    protected function commit($id)
    {
        update_post_meta($id, $this->key, $this->json());
    }

    /**
     * Get the review data as JSON.
     *
     * @return string
     */
    protected function json()
    {
        return json_encode($this->data);
    }

    /**
     * Get the history entries for our metabox.
     *
     * @return array
     */
    protected function history()
    {
        $users = $this->getUsersforHistory();

        return array_map(function ($entry) use ($users) {
            $entry['user'] = $users[$entry['user']];

            return $entry;
        }, $this->data['history']);
    }

    /**
     * Get a map of users for the history entries.
     *
     * @return array
     */
    protected function getUsersforHistory()
    {
        $ids = array_map(function ($entry) {
            return $entry['user'];
        }, $this->data['history']);

        $map = [];

        foreach (get_users(['include' => array_unique($ids)]) as $user) {
            $map[$user->ID] = "{$user->first_name} {$user->last_name}";
        }

        return $map;
    }

    /**
     * Get the ID of the current user.
     *
     * @return int
     */
    protected function userId()
    {
        return get_current_user_id();
    }
}
