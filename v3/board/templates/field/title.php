<script class="template" type="t/template" data-id="field-title">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-title col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">

		<div class="wrapper-contenteditable">
			<div class="contenteditable-prevent-click">&#8203;</div>
			<div class="form-control highlight"
			    {{isCardWrite}}contenteditable="true"{{:isCardWrite}}readonly{{/isCardWrite}}
				data-placeholder="{{field.options.placeholder}}{{%field.options.placeholder}}{{:field.options.placeholder}}&#8203;{{/field.options.placeholder}}"
				onfocus="kanban.fields[{{%field.id}}].onFocus(this);"
				onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"
				onblur="kanban.fields[{{%field.id}}].onBlur(this, event);">{{=fieldvalue.content}}</div>
				<div class="contenteditable-prevent-click">&#8203;</div>
		</div>
	</div>

</script>