<?php

class HamodiaThumbnail
{
    /**
     * The post for which to get the thumbnail.
     *
     * @var WP_Post
     */
    protected $post;

    /**
     * Constructor.
     *
     * @param WP_Post  $post
     */
    public function __construct(WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * Make a new instance.
     *
     * @param  WP_Post  $post
     * @return static
     */
    public static function make(WP_Post $post)
    {
        return new static($post);
    }

    /**
     * Get the thumbnail info.
     *
     * @param  string  $size
     * @param  string  $class
     * @param  bool|null  $details
     * @return string|array|null
     */
    public function get($size = 'thumbnail', $class = 'excerpt-thumbnail', $details = null)
    {
        if (is_null($properties = $this->getImageProperties($size))) {
            return;
        }

        $attributes = $this->attributes(array(
            'class'  => $class." $size-size",
            'src'    => $properties[0],
            'width'  => $properties[1],
            'height' => $properties[2],
        ));

        return $this->compileImage($properties, $attributes, $details);
    }

    /**
     * Get the properties for the first image in the post.
     *
     * @param  string  $size
     * @return array|null
     */
    protected function getImageProperties($size)
    {
        if (is_null($id = $this->getImageId())) {
            return;
        }

        return wp_get_attachment_image_src($id, $size);
    }

    /**
     * Get the id for the first image in the post.
     *
     * @return int|null
     */
    protected function getImageId()
    {
        return $this->getInlineImageId() ?: $this->getGalleryImageId();
    }

    /**
     * Get the id for the first inline image in the post.
     *
     * @return int|null
     */
    protected function getInlineImageId()
    {
        $pattern = '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i';

        if (preg_match($pattern, $this->post->post_content, $matches)) {
            return get_image_id_from_link($matches[1]);
        }
    }

    /**
     * Get the id for the first gallery image in the post.
     *
     * @return int|null
     */
    protected function getGalleryImageId()
    {
        $pattern = '/\[gallery ids="([\d]+)/';

        if (preg_match($pattern, $this->post->post_content, $matches)) {
            return $matches[1];
        }
    }

    /**
     * Determine whether to return the image's details.
     *
     * @param  bool|null  $details
     * @return bool
     */
    protected function shouldReturnDetails($details = null)
    {
        if (is_null($details)) {
            return has_post_format('image', $this->post);
        }

        return (bool) $details;
    }

    /**
     * Compile the image to html, optionally with additional info.
     *
     * @param  array  $$properties
     * @param  string  $attributes
     * @param  bool|null  $details
     * @return string|array
     */
    protected function compileImage(array $properties, $attributes, $details)
    {
        if (! $this->shouldReturnDetails($details)) {
            return "<img $attributes>";
        }

        return [
            'img'     => "<img $attributes>",
            'caption' => null,
            'props'   => [
                'src'    => $properties[0],
                'width'  => $properties[1],
                'height' => $properties[2],
            ],
        ];
    }

    /**
     * Compile a list of attributes into html.
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
