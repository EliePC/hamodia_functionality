<?php

class HamodiaGallery
{
    /**
     * The attributes provided to the shortcode.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Constructor.
     *
     * @param array  $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Make a new instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function make(array $attributes)
    {
        return new static($attributes);
    }

    /**
     * Render the actual gallery.
     *
     * @return string
     */
    public function render()
    {
        $items = implode('', $this->getImages());

        return '<div class="gallery">'.$items.'</div>';
    }

    /**
     * Get the images in the gallery.
     *
     * @return array
     */
    protected function getImages()
    {
        $images = $this->queryImages();

        return array_map(function ($image) {
            return $this->getImageHtml($image);

        }, $images);
    }

    /**
     * Query for the images in the gallery.
     *
     * @return WP_Post[]
     */
    protected function queryImages()
    {
        return get_posts([
            'include'        => $this->attributes['include'],
            'orderby'        => $this->attributes['orderby'],
            'order'          => 'ASC',
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
        ]);
    }

    /**
     * Get the HTML for the given image.
     *
     * @param  WP_Post  $image
     * @return string
     */
    protected function getImageHtml(WP_Post $image)
    {
        $properties = wp_get_attachment_image_src($image->ID, 'full');

        $link = wp_get_attachment_link($image->ID, 'thumbnail');

        $attributes = $this->attributes([
            'data-caption' => esc_attr($image->post_excerpt),
            'data-width'   => $properties[1],
            'data-height'  => $properties[2],
            'class'        => 'gallery-item',
        ]);

        return "<div $attributes>$link</div>";
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
