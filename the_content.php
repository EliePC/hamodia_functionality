<?php

class Hamodia_the_content
{
    protected static $DOMDocument, $share_buttons;

    public static $been_used = false;

    public static function init($post, $content)
    {
        static::$been_used = true;

        static::setup_html($content);

        if (! has_post_format('gallery', $post)) {
            static::control_images();
        }

        static::add_buttons($post);
        static::add_meta_info();
        static::add_state_and_source($post);

        return static::get_content();
    }

    protected static function setup_html($content)
    {
        $content = static::unautopImages($content);

        // Convert special characters: http://stackoverflow.com/questions/11309194/php-domdocument-failing-to-handle-utf-8-characters#answer-11310258
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        static::$DOMDocument = new DOMDocument('1.0', 'utf-8');
        // Supress HTML5 element errors
        libxml_use_internal_errors(true);
        static::$DOMDocument->loadHTML($content);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
    }

    private static function unautopImages($content)
    {
        $pattern = '/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s';

        return preg_replace($pattern, '<figure>$1</figure>', $content);
    }

    protected static function control_images()
    {
        $images = static::$DOMDocument->getElementsByTagName('img');

        if (! $images->length) {
            return;
        }

        foreach ($images as $i => $image) {
            if ($i == 0) {
                static::format_main_image($image);
            } else {
                static::wrap_image($image, has_post_format('image'));
            }
        }
    }

    protected static function format_main_image($image)
    {
        $body = static::$DOMDocument->getElementsByTagName('body')->item(0);

        // 1. Wrap the image with a link
        static::wrap_image($image, true);

        // 2. Hoist it (or its parent figure) to the top & wrap it in a figure and another outer wrapper
        $anchor = $image->parentNode;
        $parent = $anchor->parentNode;

        $parent->setAttribute('style', '');

        if ($parent->tagName == 'figure') {
            $parent->setAttribute('class', 'main-image');
        } else {
            $parent->setAttribute('class', 'no-caption');

            // create a new figure element
            $figure = static::$DOMDocument->createElement('figure');
            $figure->setAttribute('class', 'main-image');

            static::wrap($anchor, $figure);

            $parent = $figure;
        }

        $wrapper = static::$DOMDocument->createElement('div');
        $wrapper->setAttribute('class', 'main-image-and-buttons-wrapper');

        static::wrap($parent, $wrapper);

        // Move this image (and its wrappers) to the top of the document
        if ($body->firstChild !== $wrapper) {
            static::prepend($body, $wrapper);
        }
    }

    protected static function wrap_image($image, $enlarge = false)
    {
        $image_id = get_image_id_from_link($image->getAttribute('src'));

        if ($enlarge) {
            list($src, $width, $height) = wp_get_attachment_image_src($image_id, 'large');

            $image->setAttribute('src', $src);
            $image->setAttribute('width', $width);
            $image->setAttribute('height', $height);
        }

        $anchor = $image->parentNode;

        // If the image is not wrapped in a link
        if ($anchor->tagName != 'a') {
            $anchor = static::$DOMDocument->createElement('a');
            // and wrap the image with the link
            static::wrap($image, $anchor);
        }

        // We'll make sure it links to the "large" image
        list($src, $width, $height) = wp_get_attachment_image_src($image_id, 'large');

        $anchor->setAttribute('href', $src);
        // We'll set the data-width & data-height attributes to be used by photoswipe.js
        $anchor->setAttribute('data-width', $width);
        $anchor->setAttribute('data-height', $height);

        // If we're enlarging the image, we have to remove the `max-width` from the inline styles
        if ($enlarge && $anchor->parentNode->tagName == 'figure') {
            $anchor->parentNode->setAttribute('style', '');
        }

        return $image;
    }

    protected static function add_buttons($post)
    {
        $first_child = static::get_first_element(static::$DOMDocument->childNodes);

        // We have to call `get_share_buttons` even if we end up not using it,
        // since the `get_share_buttons` is also called from outside this class,
        // when `$DOMDocument` has already been destroyed
        $share_buttons = static::str_to_element(static::get_share_buttons($post));

        // Since we have these share buttons at the bottom of the post,
        // we only want the share buttons here if there's more than 4 paragraphs.
        if (static::$DOMDocument->getElementsByTagName('p')->length < 5) {
            return;
        }

        // If we've got a picture
        if ($first_child->getAttribute('class') == 'main-image-and-buttons-wrapper') {
            // we add the share buttons to the image wrapper
            $first_child->appendChild($share_buttons);
        } else {
            // No image wrapper? We create our own
            $wrapper = static::$DOMDocument->createElement('div');
            $wrapper->setAttribute('class', 'main-image-and-buttons-wrapper no-image');

            static::prepend($first_child->parentNode, $share_buttons, $first_child);

            static::wrap($share_buttons, $wrapper);
        }
    }

    protected static function add_meta_info()
    {
        static::prepend(
            static::$DOMDocument->getElementsByTagName('body')->item(0),
            hamodia_entry_meta(false)
        );
    }

    protected static function add_state_and_source($post)
    {
        $state  = get_post_meta($post->ID, '_hamodia_post_meta_state',  true);
        $source = get_post_meta($post->ID, '_hamodia_post_meta_source', true);

        if (! $state && ! $source) {
            return;
        }

        $first_paragraph = static::$DOMDocument->getElementsByTagName('p')->item(0);

        if (! $first_paragraph) {
            return;
        }

        // Add source
        static::prepend($first_paragraph, static::$DOMDocument->createTextNode($source ? " ($source) - " : " - "));

        // Add state
        $state && static::prepend($first_paragraph, static::$DOMDocument->createElement('b', $state));
    }

    public static function get_share_buttons($post)
    {
        if (static::$share_buttons) {
            return static::$share_buttons;
        }

        // Article email & print
        $subject  = 'Hamodia.com - ' . get_the_title($post->ID);
        $body     = static::get_email_body();


        $html  = '<ul class="share-buttons">';

        // Print
        $html .= '<li class="print">';
        $html .=    '<a href="#" title="Print this" onclick="window.print();return false;">Print</a>';
        $html .= '</li>';

        // Email
        $html .= '<li class="email">';
        $html .=    '<a href="mailto:?subject=';
        $html .=        esc_attr($subject);
        $html .=        '&amp;body=';
        $html .=        esc_attr($body);
        $html .=        '" title="Send via email">';
        $html .=        'Email';
        $html .=    '</a>';
        $html .= '</li>';

        // Gmail
        $html .= '<li class="gmail">';
        $html .=    '<a href="http://mail.google.com/mail/?view=cm&amp;fs=1&amp;su=';
        $html .=        urlencode($subject);
        $html .=        '&amp;body=';
        $html .=        urlencode($body);
        $html .=        '" title="Send via gmail" target="_blank">';
        $html .=        'Gmail';
        $html .=        '</a>';
        $html .= '</li>';

        $html .= '</ul>';

        return static::$share_buttons = $html;
    }

    protected static function get_email_body()
    {
        $body_word_count = 250;

        $paragraphs = static::$DOMDocument->getElementsByTagName('p');
        $length     = $paragraphs->length;
        $text       = '';

        for ($i = 0; $i < $length; $i++) {
            if ($i != 0) {
                $text .= ' ';
            }

            $text .= $paragraphs->item($i)->textContent;

            if (strlen($text) >= $body_word_count) {
                break;
            }
        }

        // Truncate text,
        $text = substr($text, 0, $body_word_count);
        // Remove broken words,
        $text = substr($text, 0, strrpos($text, ' '));
        // Add elipsis
        $text .= '...';
        // Add link
        $text .= ' - ' . get_permalink();

        return $text;
    }

    protected static function get_content()
    {
        $doc = new DOMDocument('1.0', 'utf-8');

        $doc->appendChild($doc->importNode(
            static::$DOMDocument->getElementsByTagName('body')->item(0), true
        ));

        $content = preg_replace('~^<body[^>]*>|</body>$~', '', trim($doc->saveHTML()));

        static::$DOMDocument = null;

        return $content;
    }

    protected static function wrap($el, $wrapper)
    {
        if (is_string($wrapper)) {
            $wrapper = static::str_to_element($wrapper);
        }

        $parent = $el->parentNode;

        if (! $parent) {
            $wrapper->appendChild($el);
        } else {
            $parent->replaceChild($wrapper, $el);

            $wrapper->appendChild($el);
        }
    }

    protected static function prepend($parent, $el, $firstChild = null)
    {
        if (is_string($el)) {
            $el = static::str_to_element($el);
        }

        // If `$el` is a collection of elements
        if ($el instanceof DOMNodeList) {
            // we loop in reverse, and prepend each one separately
            $i = $el->length;
            while ($i--) {
                static::prepend($parent, $el->item($i));
            }
        }

        $firstChild = $firstChild ?: $parent->childNodes->item(0);
        $parent->insertBefore($el, $firstChild);
    }

    protected static function get_first_element(DOMNodeList $list)
    {
        foreach ($list as $node) {
            if ($node->nodeType === 1) {
                return $node;
            }
        }
    }

    protected static function str_to_element($html)
    {
        // Convert special characters: http://stackoverflow.com/questions/11309194
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $dom = new DOMDocument('1.0', 'utf-8');

        // Supress HTML5 element errors
        libxml_use_internal_errors(true);
        $dom->loadHTML(utf8_encode($html));
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $body = static::$DOMDocument->importNode($dom->getElementsByTagName('body')->item(0), true);

        // For some odd reason, the node has to be inserted into the DOM first:
        // http://stackoverflow.com/questions/14695303/accessing-an-imported-element-after-the-original-domdocument-is-destroyed
        $element = static::$DOMDocument->documentElement->appendChild($body->childNodes->item(0));
        return static::$DOMDocument->documentElement->removeChild($element);
    }
}

add_filter('the_content', function ($content) {
    if (
        Hamodia_the_content::$been_used ||
        ! is_main_query() ||
        ! is_single() ||
        is_feed()
    ) {
        return $content;
    }

    // HACK: We don't want this filter to run when getting the_excerpt so
    // we check the backtrace if we got here through wp_trim_excerpt
    $backtrace = debug_backtrace();

    if ($backtrace[3]['function'] == 'wp_trim_excerpt') {
        return $content;
    }

    return Hamodia_the_content::init($GLOBALS['post'], $content);
}, 12 /* after shortcodes, which is 11 */);
