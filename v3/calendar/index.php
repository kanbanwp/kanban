<?php

$app_data = Kanban_App::instance()->get_app_data();

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow"/>

	<link rel="stylesheet" href="<?php echo Kanban::instance()->settings()->uri ?>css/elegant-icons.min.css?<?php echo $app_data->plugin_data['Version'] ?>">
	<link rel="stylesheet" href="<?php echo Kanban::instance()->settings()->uri ?>css/bootstrap.min.css?<?php echo $app_data->plugin_data['Version'] ?>">
	<link rel="stylesheet" href="<?php echo Kanban_Router::instance()->get_uri('calendar') ?>/css/app.css?<?php echo $app_data->plugin_data['Version'] ?>">

</head>
<body>

<div class="container">
<div id="calendar"></div>
</div>


<script src="/wp-content/plugins/kanban-pro/v3/calendar/js/app.js"></script>

</body>
</html>
