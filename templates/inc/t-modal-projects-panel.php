<div class="panel panel-default" data-id="{{=ID}}">
	<a class="panel-heading" data-toggle="collapse" data-parent="#accordion-projects" href="#collapse-{{=ID}}">
		{{=post_title}}
	</a>
	<div id="collapse-{{=ID}}" class="panel-collapse collapse">
		<div class="panel-body">
			<p>
				There are {{=task_count}} tasks assigned to this project.
			</p>
			<p class="form-group">
				<input type="text" class="form-control input-lg project_title" value="{{=post_title}}">
			</p>
			<p class="text-right">
				<a href="#" class="text-danger btn-delete">
					Delete this project
				</a>
			</p>
		</div><!-- panel body -->
	</div>
</div>