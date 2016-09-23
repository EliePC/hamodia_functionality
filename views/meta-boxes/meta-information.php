<style>
#hamodia_post_meta label, #hamodia_post_meta_print_edition label {
	display: inline-block;
	width: 50px;
}
</style>

<label for="hamodia_post_meta_state">State:</label>
<input type="text"
       id="hamodia_post_meta_state"
       name="hamodia_post_meta_state"
       value="<?= get_post_meta($post->ID, '_hamodia_post_meta_state', true); ?>"
       size="30"
>

<br>

<label for="hamodia_post_meta_source">Source:</label>
<input type="text"
       id="hamodia_post_meta_source"
       name="hamodia_post_meta_source"
       value="<?= get_post_meta($post->ID, '_hamodia_post_meta_source', true); ?>"
       size="30"
>

<br>

<label for="hamodia_post_meta_author">Author:</label>
<input type="text"
       id="hamodia_post_meta_author"
       name="hamodia_post_meta_author"
       value="<?= get_post_meta($post->ID, '_hamodia_post_meta_author', true); ?>"
       size="30"
>
