<label for="hamodia_post_meta_date">Date:</label>
<input type="text"
       id="hamodia_post_meta_date"
       name="hamodia_post_meta_date"
       value="<?= get_post_meta($post->ID, '_hamodia_post_meta_date', true); ?>"
       placeholder="MM/DD/YYYY"
       size="30"
>
<br>
<label for="hamodia_post_meta_page">Page:</label>
<input type="text"
       id="hamodia_post_meta_page"
       name="hamodia_post_meta_page"
       value="<?= get_post_meta($post->ID, '_hamodia_post_meta_page', true); ?>"
       size="30"
>
