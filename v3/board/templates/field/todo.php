<script class="template" type="t/template" data-id="field-todo">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-todo col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="list-group">
			{{=todosHtml}}
			<div class="list-group-item add-new-todo">
				<div class="form-control task-content add-new-todo"
				     contenteditable="true" placeholder="Add a new item"
				     onblur="kanban.fields[{{%field.id}}].onBlur(this, event);"
				     onfocus="kanban.fields[{{%field.id}}].onFocus(this, event);"
				     onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"></div>
			</div>
		</div>
	</div>

</script>