jQuery(function($) {
	var tomorrow = new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
	// Now format the date
	tomorrow = (tomorrow.getMonth() + 1) + '/' + tomorrow.getDate() + '/' + tomorrow.getFullYear();

	$('#hamodia_post_meta_date').datepicker().focus(function()
	{
		if ( ! this.value) this.value = tomorrow;
	});

	// Edit post screen uses `categorychecklist`, while the quick edit screen uses `category-checklist`. Go figure.
	$(document).on('change', '.category-checklist input, .categorychecklist input', function ()
	{
		var $this = $(this);

		if (this.checked) {
			// We don't want multiple categories per story (unless it's OP-ED)
			$this.closest('.category-checklist, .categorychecklist')
				.find( 'input' + ($this.is('input[value=16]') ? ':not(input[value=17], input[value=18])' : '') )
				.not(this)
				.prop('checked', false);
		}

		// If we're dealing with editorial/opinion, we toggle OP-ED
		if ($this.is('input[value=17], input[value=18]')) {
			$this
				.closest('.category-checklist, .categorychecklist')
				.find('input[value=16]')
				.prop('checked', this.checked)
				.change();
		}
	});
});

// The tags have been converted to a list of checkboxes, so that we don't accidentally create
// new tags. Since scrolling thru the huge list to find tags you want is quite cumbersome,
// we'll add a search box on top to be able to easily filter through the list of tags.
jQuery(function($) {
	var $wrapper = $('#taxonomy-post_tag');

	if ( ! $wrapper.length) return;

	var $search = $wrapper.find('> [type=search]');
	var $tags = $wrapper.find('#post-tag-checklist > li');
	var keys = [];
	var map = {};

	primeCache()

	$search.on('input', function() {
		var search = this.value.trim().toLowerCase();

		$tags.removeClass('hidden');

		if (search === '') return;

		getMissingKeys(search).forEach(function(key) {
			map[key].classList.add('hidden');
		});
	});

	function primeCache() {
		$tags.each(function() {
			map[$(this).text().trim().toLowerCase()] = this;
		});

		keys = Object.keys(map);
	}

	function getMissingKeys(search) {
		return keys.filter(function(key) {
			return key.indexOf(search) < 0;
		});
	}

});

// Quality control
jQuery(function($) {
	var $button = $('#publish');
	var $checks = $('#hamodia-review-checklist input');
	var $status = $('#post_status');

	var isPublished = $button.val() == 'Update';

	var $message = $('<span>QC not met</span>')
	                .css('line-height', '28px')
	                .addClass('hidden')
	                .insertAfter($button);

	toggleSubmitVisibility();

	$checks.on('change', toggleSubmitVisibility);
	$status.on('change', toggleSubmitVisibility);

	function toggleSubmitVisibility() {
		var validated = canSubmit();

		$button.toggleClass('hidden', ! validated);

		$message.toggleClass('hidden', validated);
	}

	function canSubmit() {
		if (isPublished && $status.val() != 'publish') {
			return true;
		}

		return $checks.filter(':not(:checked)').length == 0;
	}
});
