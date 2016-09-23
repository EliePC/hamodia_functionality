<?php

add_filter('login_headerurl', function () {
    return get_bloginfo('url');
});

add_filter('login_headertitle', function () {
    return 'Hamodia - The Daily Newspaper of Torah Jewry';
});

add_action('login_enqueue_scripts', function () {
    ?>
	<style type="text/css">
		body.login div#login h1 a {
			background-image: url(<?php echo get_bloginfo('template_directory') ?>/images/login-logo.png);
			background-position: center center;
			background-size: auto;
			height: 90px;
			width: 280px;
		}
	</style>
	<?php
});
