<style>
#hamodia_post_meta_homepage_placement label {
	display: block;
	margin-top: 12px;
}
</style>

<label>
	<input value="1" type="radio" name="hamodia_home_page_article"
		<?= $position === 0 ? 'checked' : null; ?>
	>
	Primary Article
</label>

<label>
	<input value="2" type="radio" name="hamodia_home_page_article"
		<?= $position === 1 ? 'checked' : null; ?>
	>
	Secondary Article 1
</label>

<label>
	<input value="3" type="radio" name="hamodia_home_page_article"
		<?= $position === 2 ? 'checked' : null; ?>
	>
	Secondary Article 2
</label>

<label>
	<input value="4" type="radio" name="hamodia_home_page_article"
		<?= $position === 3 ? 'checked' : null; ?>
	>
	Secondary Article 3
</label>

<label>
	<input value="" type="radio" name="hamodia_home_page_article"
		<?= $position === false ? 'checked' : null; ?>
	>
	None
</label>
