<script class="template" type="t/template" data-id="field-todo-task">
	<div class="list-group-item">
<!--		<a href="javascript:void(0);" class="todo-move-handle btn btn-empty pull-right ei ei-menu"></a>-->
		<input type="checkbox" class="task-checkbox"
		       onchange="kanban.fields[{{%field.id}}].onCheck(this, event);"
		       id="checkbox-{{%card_id}}-{{%field.id}}-{{%todo.index}}"
		       {{todo.is_checked}}checked{{/todo.is_checked}}>
		<label for="checkbox-{{%card_id}}-{{%field.id}}-{{%todo.index}}" class="todo-checkbox ei ei-box-empty"></label>
		<label for="checkbox-{{%card_id}}-{{%field.id}}-{{%todo.index}}" class="todo-checkbox ei ei-box-checked"></label>
		<div class="form-control task-content"
		     onblur="kanban.fields[{{%field.id}}].onBlur(this, event);"
		     onfocus="kanban.fields[{{%field.id}}].onFocus(this, event);"
		     onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"
		     contenteditable="true">{{=todo.content}}</div>
	</div>
</script>