
<div class="col col-sm-3 col-status col_percent_w" id="status-<?php echo $status_id ?>" data-id="<?php echo $status_id ?>">
	<a href="#" class="btn btn-default btn-status-toggle pull-left visible-xs" data-operator="-1">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a href="#" class="btn btn-default btn-status-toggle pull-right visible-xs" data-operator="+1">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
	<h3 class="h4" style="border-color: <?php echo $status->color_hex ?>">
		<?php echo $status->title ?>
		<sup class="status-task-count"></sup>
<?php if ( $status->wip_task_limit > 0 ) : ?>
		<sup class="status-wip-task-limit">/<?php echo $status->wip_task_limit ?></sup>
<?php endif // wip_task_limit ?>
	</h3>
	<div class="btn-group btn-group-status-actions" style="display: none;">
		<button type="button" class="btn btn-primary btn-task-new" data-status-id="<?php echo $status_id ?>">
			<span class="hidden-xs glyphicon glyphicon-plus"></span>
			<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
			<span class="visible-xs-inline">Add a task</span>
		</button>
<?php $status_id_arr = array_keys($board->statuses); if ( $status_id == reset($status_id_arr) || $status_id == end($status_id_arr) ) : ?>
	<button type="button" class="hidden-xs btn btn-primary btn-status-empty" data-status-id="<?php echo $status_id ?>">
		<span class="glyphicon glyphicon-trash"></span>
		<span class="hidden-xs glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
	</button>
<?php endif ?>
	</div>
</div><!-- col -->