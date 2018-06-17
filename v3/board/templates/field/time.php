<script class="template" type="t/template" data-id="field-time">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-time col col-sm-12"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="clearfix">
			<div class="col xxcol-sm-6 col-hours" data-label="<?php _e('h', 'kanban') ?>">

				<input class="form-control form-control-hours" type="number" step="{{%fieldOptions.step}}"
				       min="0"
				       onfocus="kanban.fields[{{%field.id}}].onFocus(this, event);"
				       onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"
				       onblur="kanban.fields[{{%field.id}}].onBlur(this, event);"
				       value="{{%fieldvalue.content.hours}}"></input>
				<div class="btn-group btn-group-justified">
					<a class="btn btn-default btn-sm"
					   href="javascript:void(0);"
					   data-input="hours"
					   data-operator="1"
					   onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-plus"></i>
					</a>
					<a class="btn btn-default btn-sm"
					   href="javascript:void(0);"
					   data-input="hours"
					   data-operator="-1"
					   onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-minus-06"></i>
					</a>
				</div>

			</div>

			{{fieldOptions.show_estimate}}
			
			<div class="col">
				/
			</div>

			<div class="col xxcol-sm-6 col-estimate" data-label="<?php _e('h', 'estimate') ?>">

				<input class="form-control form-control-estimate" type="number" step="{{%fieldOptions.step}}"
				       min="0"
				       onfocus="kanban.fields[{{%field.id}}].onFocus(this, event);"
				       onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"
				       onblur="kanban.fields[{{%field.id}}].onBlur(this, event);"
				       value="{{%fieldvalue.content.estimate}}"></input>
				<div class="btn-group btn-group-justified">
					<a class="btn btn-default btn-sm"
					   href="javascript:void(0);"
					   data-input="estimate"
					   data-operator="1"
					   onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-plus"></i>
					</a>
					<a class="btn btn-default btn-sm"
					   href="javascript:void(0);"
					   data-input="estimate"
					   data-operator="-1"
					   onclick="kanban.fields[{{%field.id}}].onClickbutton(this);">
						<i class=" ei ei-minus-06"></i>
					</a>
				</div>
			</div>

			{{/fieldOptions.show_estimate}}

		</div><!--row-->

		{{fieldOptions.show_estimate}}
		<div class="progress">
			<div class="progress-bar" style="width: {{%percentage}}%;">
			</div>
		</div>
		{{/fieldOptions.show_estimate}}

	</div>

</script>