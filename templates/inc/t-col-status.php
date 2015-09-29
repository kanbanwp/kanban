<div class="col col-xs-12 col-status col_percent_w" id="status-{{=term_id}}" data-id="{{=term_id}}"
     data-color="{{=color}}" data-close="{{=left_close}}%" data-open="{{=left_open}}%">

	{{left_close}}
	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-close">
		{{=name}}
		<span class="glyphicon glyphicon-chevron-right"></span>
	</button>

	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-open">
		<span class="glyphicon glyphicon-chevron-left"></span>
		{{=name}}
	</button>
	{{/left_close}}

	<h2 class="h3" style="border-color: {{=color}};">
		{{=name}}
	</h2>

	<div class="btn-group btn-group-status-actions pull-right">
		<button type="button" class="btn btn-primary btn-new-task">
			<span class="glyphicon glyphicon-plus"></span>
			<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
		</button>
	</div>

</div><!-- col -->
