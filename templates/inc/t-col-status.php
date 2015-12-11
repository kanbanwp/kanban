<div class="col col-xs-12 col-status col_percent_w" id="status-{{=id}}" data-id="{{=id}}" data-color="{{=color_hex}}" data-close="{{=left_close}}%" data-open="{{=left_open}}%">

{{left_close}}
	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-close">
		{{=title}}
		<span class="glyphicon glyphicon-chevron-right"></span>
	</button>

	<button type="button" class="btn btn-primary toggle-sidebar toggle-sidebar-open">
		<span class="glyphicon glyphicon-chevron-left"></span>
		{{=title}}
	</button>
{{/left_close}}

	<h2 class="h3" style="border-color: {{=color_hex}};">
		{{=title}}
	</h2>

	<div class="btn-group btn-group-status-actions pull-right">
		<button type="button" class="btn btn-primary btn-new-task">
			<span class="glyphicon glyphicon-plus"></span>
			<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate" style="display: none;"></span>
		</button>
	</div>

</div><!-- col -->
