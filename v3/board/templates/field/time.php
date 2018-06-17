<script class="template" type="t/template" data-id="field-time">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-time col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="row">
			<div class="col col-sm-6">

				<input class="form-control form-control-hours" type="number" step="{{%fieldOptions.step}}"
				       min="0"
				       onfocus="kanban.fields[{{%field.id}}].onFocus(this);"
				       onkeydown="kanban.fields[{{%field.id}}].onKeydown(this);"
				       onblur="kanban.fields[{{%field.id}}].onBlur(this);"
				       value="{{%fieldvalue.content.hours}}"></input>
				<div class="btn-group">
					<button class="btn btn-default btn-sm"
					        data-input="hours"
					        data-operator="1"
					        onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-plus"></i>
					</button>
					<button class="btn btn-default btn-sm"
					        data-input="hours"
					        data-operator="-1"
					        onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-minus-06"></i>
					</button>
				</div>

			</div>

			<div class="col col-sm-6">

				{{fieldOptions.show_estimate}}
				<input class="form-control form-control-estimate" type="number" step="{{%fieldOptions.step}}"
				       min="0"
				       onfocus="kanban.fields[{{%field.id}}].onFocus(this);"
				       onkeydown="kanban.fields[{{%field.id}}].onKeydown(this);"
				       onblur="kanban.fields[{{%field.id}}].onBlur(this);"
				       value="{{%fieldvalue.content.estimate}}"></input>
				<div class="btn-group">
					<button class="btn btn-default btn-sm"
					        data-input="estimate"
					        data-operator="1"
					        onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-plus"></i>
					</button>
					<button class="btn btn-default btn-sm"
					        data-input="estimate"
					        data-operator="-1"
					        onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-minus-06"></i>
					</button>
				</div>
			</div>
			{{/fieldOptions.show_estimate}}

		</div><!--row-->
	</div>

</script>