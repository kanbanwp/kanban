<script class="template" type="t/template" data-id="field-todo">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-todo col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
					<div class="panel-title">
						<input type="checkbox" id="checkbox-1">
						<label for="checkbox-1" class="todo-checkbox ei ei-box-empty"></label>
						<label for="checkbox-1" class="todo-checkbox ei ei-box-checked"></label>
						<div class="todo" contenteditable="true">
							Lorem ipsum dolor sit amet, consectetur adipiscing elit.
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
					<div class="panel-title">
						<input type="checkbox" id="checkbox-2">
						<label for="checkbox-2" class="todo-checkbox ei ei-box-empty"></label>
						<label for="checkbox-2" class="todo-checkbox ei ei-box-checked"></label>
						<div class="todo" contenteditable="true">
							Lorem ipsum dolor sit amet, consectetur adipiscing elit.
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
					<div class="panel-title">
						<input type="checkbox" id="checkbox-3">
						<label for="checkbox-3" class="todo-checkbox ei ei-box-empty"></label>
						<label for="checkbox-3" class="todo-checkbox ei ei-box-checked"></label>
						<div class="todo" contenteditable="true">
							Lorem ipsum dolor sit amet, consectetur adipiscing elit.
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</script>