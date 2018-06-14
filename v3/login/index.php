<?php

// Redirect old version 2 url.
wp_redirect(
	add_query_arg(array(
			'redirect_to' => urlencode(Kanban_Router::instance()->get_page_uri('board'))
		),
		wp_login_url()
	));
exit;
