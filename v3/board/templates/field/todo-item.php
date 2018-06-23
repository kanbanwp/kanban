<script class="template" type="t/template" data-id="field-todo-item">
	<div class="list-group-item">
		<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>
		<input type="checkbox" id="checkbox-{{%card.id}}-{{%field.id}}-{{%todo.index}}" {{todo.is_active}}checked{{/todo.is_active}}>
		<label for="checkbox-{{%card.id}}-{{%field.id}}-{{%todo.index}}" class="todo-checkbox ei ei-box-empty"></label>
		<label for="checkbox-{{%card.id}}-{{%field.id}}-{{%todo.index}}" class="todo-checkbox ei ei-box-checked"></label>
		<div class="form-control todo-item" contenteditable="true">
			{{%todo.content}}
		</div>
	</div>
</script>