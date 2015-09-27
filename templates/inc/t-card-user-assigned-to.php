<div class="btn btn-default btn-assigned-to" data-toggle="dropdown">
	<span class="task-assigned-to-initials {{!ID}}empty{{/!ID}}">
{{data.avatar}}
		{{=data.avatar}}
{{:data.avatar}}
		{{=data.initials}}
{{/data.avatar}}
	</span>
	<small class="task-assigned-to-short_name {{!ID}}empty{{/!ID}}">
		{{=data.short_name}}
	</small>
	<input type="hidden" class="assigned-to-id" value="{{=ID}}">
</div>

