<div class="col col-xs-12 col-status col_percent_w" id="status-{{=status.id}}" data-id="{{=status.id}}" data-color="{{=status.color_hex}}" data-close="{{=status.left_close}}%" data-open="{{=status.left_open}}%">

{{status.left_close}}
	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-close">
		{{=status.title}}
		<span class="glyphicon glyphicon-chevron-right"></span>
	</button>

	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-open">
		<span class="glyphicon glyphicon-chevron-left"></span>
		{{=status.title}}
	</button>
{{/status.left_close}}

	<h2 class="h3" style="border-color: {{=status.color_hex}};">
		{{=status.title}}
		<sup class="task-count"></sup>
	</h2>

{{current_user_can_write}}

	<div class="btn-group btn-group-status-actions pull-right">
{{status.left_close}}
		<button type="button" class="btn btn-primary btn-empty-status-tasks-modal" data-status-title="{{=status.title}}" data-status-col-id="status-{{=status.id}}-tasks">
			<span class="glyphicon glyphicon glyphicon-trash"></span>
			<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
		</button>
{{/status.left_close}}
		<button type="button" class="btn btn-primary btn-new-task">
			<span class="glyphicon glyphicon-plus"></span>
			<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
		</button>
	</div>
{{/current_user_can_write}}
</div><!-- col -->
