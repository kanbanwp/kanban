<?php


wp_safe_redirect(add_query_arg(
	array(
		'page' => 'kanban_settings',
		'message' => urlencode(__('Customize your board here.'))
	),
	admin_url( 'admin.php' )
));

exit;