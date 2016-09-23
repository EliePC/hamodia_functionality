<?php

class HamodiaStoryUpdates
{
    /**
     * The key used for our metadata.
     *
     * @var string
     */
    protected $key = '_hamodia_story_updates';

    /**
     * The data for this post.
     *
     * @var array
     */
    protected $data;

    /**
     * The keys to use in the form input.
     *
     * @var string
     */
    protected static $inputKey = 'hamodia_story_updates';

    /**
     * The singleton instance.
     *
     * @var \HamodiaStoryUpdates[]
     */
    protected static $instances;

    /**
     * Make a new instance of this class.
     *
     * @param  int|null  $id
     * @return static
     */
    public static function make($id = null)
    {
        global $post;

        $id = $id ?: $post->id;

        if (! isset(static::$instances[$id])) {
            static::$instances[$id] = new static;
        }

        return static::$instances[$id];
    }

    /**
     * Checks whether there are any updates for the current post.
     *
     * @param  int|null  $id
     * @return bool
     */
    public static function has($id = null)
    {
        global $post;

        $id = $id ?: $post->id;

        return (bool) static::make($id)->load($id)->data;
    }

    /**
     * Gets the timestamp of the latest update.
     *
     * @param  int|null  $id
     * @return \DateTime|null
     */
    public static function latestTimestamp($id = null)
    {
        global $post;

        $id = $id ?: $post->id;

        $keys = array_keys(static::make($id)->load($id)->data);

        return static::date(array_pop($keys));
    }

    /**
     * Het the updates data for the current post.
     *
     * @param  int|null  $id
     * @return array
     */
    public static function get($id = null)
    {
        global $post;

        $id = $id ?: $post->id;

        $updates = [];

        foreach (static::make($id)->load($id)->data as $date => $body) {
            $updates[] = [
                'date' => static::date($date),
                'body' => $body,
            ];
        }

        return $updates;
    }

    /**
     * Convert a date string to a datetime instance.
     *
     * @param  string|null  $date
     * @return \DateTime|null
     */
    protected static function date($date)
    {
        if (is_null($date)) {
            return null;
        }

        $timezone = new DateTimeZone('America/New_York');

        return (new DateTime($date))->setTimezone($timezone);
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
            $id       = 'hamodia-story-updates',
            $title    = 'Story Updates',
            $callback = [$this, 'renderCustomMetaBox'],
            $screen   = 'post',
            $context  = 'advanced',
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
        $key = static::$inputKey;

        $updates = $this->fetch($post);

        require __DIR__.'/views/meta-boxes/story-updates.php';
    }

    /**
     * Load the existing data.
     *
     * @param  \WP_Post|int  $post
     * @param  bool  $force
     * @return $this
     */
    protected function load($post, $force = false)
    {
        if (is_null($this->data) || $force) {
            $this->data = $this->fetch($post);
        }

        return $this;
    }

    /**
     * Update the data from the given input.
     *
     * @param  int  $id
     * @param  array  $updates
     * @return $this
     */
    protected function update(array $input)
    {
        $this->removeDeletedUpdates($input);

        $this->updateExisting($input);

        if ($new = $this->extractNewUpdate($input)) {
            $this->data[$new['date']] = $new['body'];
        }

        return $this;
    }

    /**
     * Extract the newest update from the given input.
     *
     * @param  array  $input
     * @return array|null
     */
    protected function extractNewUpdate(array $input)
    {
        if ( ! isset($input['now']) || ! $value = trim($input['now'])) {
            return null;
        }

        return [
            'date' => (new DateTime)->format('Y-m-d\TH:i:s'),
            'body' => $value,
        ];
    }

    /**
     * Remove the deleted updates from our data.
     *
     * @param  array  $input
     * @return void
     */
    protected function removeDeletedUpdates(array $input)
    {
        foreach (array_keys($this->data) as $key) {
            if ( ! array_key_exists($key, $input)) {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * Update the existing data with the values in the given input.
     *
     * @param  array  $input
     * @return void
     */
    protected function updateExisting(array $input)
    {
        foreach (array_keys($this->data) as $key) {
            $this->data[$key] = $input[$key];
        }
    }

    /**
     * Fetch the updates data from the DB.
     *
     * @param  \WP_Post|int  $post
     * @return array
     */
    protected function fetch($post)
    {
        $id = $post instanceof WP_Post ? $post->ID : $post;

        $value = get_post_meta($id, $this->key, true);

        if ($value) {
            return json_decode($value, true) ?: [];
        }

        return [];
    }

    /**
     * Save the updates data to the DB.
     *
     * @param  int  $id
     * @return void
     */
    protected function commit($id)
    {
        update_post_meta($id, $this->key, json_encode($this->data));
    }
}
