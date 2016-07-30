<!DOCTYPE html>
<html>
<head>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese" rel="stylesheet">

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>

	<title><?php echo __( 'Kanban for WordPress', 'kanban' ); ?></title>

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<?php Kanban_Template::add_style($wp_query->query_vars['kanban']->slug); ?>
</head>
<body class="">



<?php echo apply_filters( 'kanban_page_header_after', '' ); ?>
