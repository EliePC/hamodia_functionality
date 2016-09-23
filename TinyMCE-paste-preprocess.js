function (plugin, args) {
    var whitelist = 'p, br, span, b, strong, i, em, h3, h4, h5, h6, ul, li, ol';
    var $wrapper = jQuery('<div>' + convertNonbreakingSpaces(args.content) + '</div>');
    var $elements = $wrapper.find('*');

    $elements.not(whitelist).each(function(i, element) {
        var $element = jQuery(element);
        var $contents = $element.contents();

        $contents.length ? $contents.unwrap() : $element.remove();
    });

    $elements.removeAttr('id').removeAttr('class').removeAttr('style');

    args.content = $wrapper.html();

    function convertNonbreakingSpaces(content) {
        return content.replace(/nbsp;|\u00A0/g, ' ');
    }
}
