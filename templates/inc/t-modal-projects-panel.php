<div class="panel panel-default" data-id="{{=id}}">
	<a class="panel-heading" data-toggle="collapse" data-parent="#accordion-projects" href="#collapse-{{=id}}">
		{{=title}}
	</a>
	<div id="collapse-{{=id}}" class="panel-collapse collapse">
		<div class="panel-body">
			<p>
				There are {{=task_count}} tasks assigned to this project.
			</p>
			<p class="form-group">
				<input type="text" class="form-control input-lg project_title" value="{{=title}}">
			</p>
			<p class="text-right">
				<a href="#" class="text-danger btn-delete">
					Delete this project
				</a>
			</p>
		</div><!-- panel body -->
	</div>
</div>
