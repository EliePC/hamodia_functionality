<?php

class HomepagePlacement
{
    /**
     * The meta box id.
     *
     * @var string
     */
    protected $id = 'hamodia_post_meta_homepage_placement';

    /**
     * The database option key.
     *
     * @var string
     */
    protected $optionKey = 'hamodia_home_page_articles';

    /**
     * The array of post IDs to feature on the homepage.
     *
     * @var array
     */
    protected $placement;

    /**
     * Make a new static instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static;
    }

    /**
     * Get the array of post IDs to feature on the homepage.
     *
     * @return array
     */
    public function get()
    {
        return array_filter($this->getPlacement(), function ($id) {
            return ! wp_is_post_autosave($id);
        });
    }

    /**
     * Register the meta box.
     *
     * @return void
     */
    public function register()
    {
        $this->addMetaBox($this->id, 'Homepage Placement', function (WP_Post $post) {
            $position = array_search($post->ID, $this->getPlacement());

            include __DIR__.'/views/meta-boxes/homepage-placement.php';
        });
    }

    /**
     * Register the callback for the "delete_post" hook.
     *
     * @return void
     */
    public function registerOnDelete()
    {
        $this->addAction('trashed_post', [$this, 'remove']);
        $this->addAction('deleted_post', [$this, 'remove']);
    }

    /**
     * Update the placement option.
     *
     * @param  int  $id
     * @param  int|null  $newPosition
     * @return void
     */
    public function update($id, $newPosition)
    {
        $oldPosition = $this->getPosition($id);

        if (empty($newPosition)) {
            return $this->updateRemove($oldPosition)->commit();
        }

        $this->updateAdd($id, $newPosition, $oldPosition)->commit();
    }

    /**
     * Remove a post with the given ID from the homepage placement.
     *
     * @param  int  $id
     * @return void
     */
    public function remove($id)
    {
        $this->updateRemove($this->getPosition($id))->commit($position);
    }

    /**
     * Update the option by removing the post at the given position.
     *
     * @param  int|bool  $oldPosition
     * @return $this
     */
    protected function updateRemove($oldPosition)
    {
        if ($oldPosition === false) {
            return $this;
        }

        return $this->removeAt($oldPosition);
    }

    /**
     * Update the option by adding the given post ID.
     *
     * @param  int  $id
     * @param  int|null  $newPosition
     * @param  int|bool  $oldPosition
     * @return $this
     */
    protected function updateAdd($id, $newPosition, $oldPosition)
    {
        if ($oldPosition !== false) {
            $this->removeAt($oldPosition);
        }

        return $this->addAt($newPosition - 1, $id);
    }

    /**
     * Remove the post ID at the given position.
     *
     * @param  int  $position
     * @return $this
     */
    protected function removeAt($position)
    {
        $ids = $this->getPlacement();

        array_splice($ids, $position, 1);

        $this->placement = $ids;

        return $this;
    }

    /**
     * Add the post ID at the given position.
     *
     * @param  int  $position
     * @param  int  $id
     * @return $this
     */
    protected function addAt($position, $id)
    {
        $ids = $this->getPlacement();

        array_splice($ids, $position, 0, [$id]);

        $this->placement = array_slice($ids, 0, 4);

        return $this;
    }

    /**
     * Get the placement index for the given post ID.
     *
     * @param  int  $id
     * @return int|bool
     */
    protected function getPosition($id)
    {
        return array_search($id, $this->getPlacement());
    }

    /**
     * Get the current placement option.
     *
     * @return array
     */
    protected function getPlacement()
    {
        if (is_null($this->placement)) {
            $this->placement = (array) get_option($this->optionKey);
        }

        return $this->placement;
    }

    /**
     * Commit the current placement array to the database.
     *
     * @return void
     */
    protected function commit()
    {
        update_option($this->optionKey, $this->placement);
    }

    /**
     * Register a meta box with Wordpress.
     *
     * @param string  $id
     * @param string  $title
     * @param callable  $callback
     * @param string|null  $postType
     */
    protected function addMetaBox($id, $title, callable $callback, $postType = 'post')
    {
        add_meta_box($id, $title, $callback, $postType);
    }

    /**
     * Register a meta box with Wordpress.
     *
     * @param  string  $id
     * @param  callable  $callback
     * @param  int  $priority
     * @return void
     */
    protected function addAction($name, callable $callback, $priority = 10)
    {
        add_action($name, $callback, $priority);
    }
}
