<script class="template" type="t/template" data-id="field-todo">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-todo col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="list-group">
			<div class="list-group-item">
				<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
				<input type="checkbox" id="checkbox-1">
				<label for="checkbox-1" class="todo-checkbox ei ei-box-empty"></label>
				<label for="checkbox-1" class="todo-checkbox ei ei-box-checked"></label>
				<div class="form-control" contenteditable="true">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare purus. Ut eu ante odio.
					Interdum et malesuada fames ac ante ipsum primis in faucibus. Proin ac mi urna. Quisque maximus quam
					sit amet auctor laoreet. Aenean sed porttitor nisi. Aenean pretium turpis ante, et fringilla sapien
					congue eu.
				</div>
			</div>
			<div class="list-group-item">
				<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
				<input type="checkbox" id="checkbox-2">
				<label for="checkbox-2" class="todo-checkbox ei ei-box-empty"></label>
				<label for="checkbox-2" class="todo-checkbox ei ei-box-checked"></label>
				<div class="form-control" contenteditable="true">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit.
				</div>
			</div>
			<div class="list-group-item">
				<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
				<input type="checkbox" id="checkbox-3">
				<label for="checkbox-3" class="todo-checkbox ei ei-box-empty"></label>
				<label for="checkbox-3" class="todo-checkbox ei ei-box-checked"></label>
				<div class="form-control" contenteditable="true">
					Lorem ipsum dolor sit amet, consectetur adipiscing elit.
				</div>
			</div>
			<div class="list-group-item">
				<div class="form-control" contenteditable="true" placeholder="Add a new item"></div>
			</div>
		</div>
	</div>

</script>