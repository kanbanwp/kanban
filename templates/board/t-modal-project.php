<div class="panel panel-default" id="modal-projects-{{=id}}" data-id="{{=id}}">
	<div class="panel-heading">
		<a class="panel-title" data-toggle="collapse" data-parent="#accordion-projects" href="#collapse-{{=id}}">
			<span class="label-project-title">{{=title}}</span>
			<sup>
				{{=task_count}}
			</sup>
		</a>
	</div>
	<div id="collapse-{{=id}}" class="panel-collapse collapse">
		<div class="panel-body">
			<p>
				There are {{=task_count}} tasks assigned to this project.
			</p>
			<p class="form-group">
				<input type="text" class="form-control input-lg project-title" value="{{=title}}" data-id="{{=id}}">
			</p>
			<p class="text-right">
				<a href="#" class="text-danger btn-delete" data-id="{{=id}}">
					Delete this project
				</a>
			</p>
		</div><!-- panel body -->
	</div>
</div>