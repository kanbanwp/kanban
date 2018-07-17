<script class="template" type="t/template" data-id="field-date">

	<div class="field field-{{%field.id}} field-{{%card.id}}-{{%field.id}} form-group form-group-date col
	col-sm-{{field.options.view_layout_width}}{{%field.options.view_layout_width}}{{:field.options.view_layout_width}}12{{/field.options.view_layout_width}}"
	     data-id="{{%field.id}}"
	     data-card-id="{{%card.id}}"
	     data-fieldvalue-id="{{%fieldvalue.id}}">
		{{field.label}}
		<label>{{=field.label}}:</label>
		{{/field.label}}

		<div class="row">
			<div class="col col-xs-5">
				<input type="text"
				   readonly
				   xxplaceholder="{{field.options.placeholder}}{{%field.options.placeholder}}{{:field.options.placeholder}}Select a date{{/field.options.placeholder}}"
				   class="form-control"
			       data-name="start"
				   {{isCardWrite}}data-provide="datepicker"{{/isCardWrite}}
				   data-date-format="{{field.options.format}}{{%field.options.format}}{{:field.options.format}}mm/dd/yyyy{{/field.options.format}}"
				   data-date-autoclose="true"
				   xxdata-onchange="kanban.fields[{{%field.id}}].onChange(this);"
				   value="{{%fieldvalue.content.start}}">
				<?php /*
				{{field.options.show_datecount}}
				<span class="datetimeago" data-datetime="{{=field.timeago_dt_gmt}}">
					{{=field.timeago_dt}}
				</span>
				{{/field.options.show_datecount}}
*/ ?>
			</div>

			{{field.options.is_date_range}}
			<div class="col col-xs-1 col-divider">
				&rarr;
			</div>

			<div class="col col-xs-5">
				<input type="text"
			       readonly
			       xxplaceholder="{{field.options.placeholder}}{{%field.options.placeholder}}{{:field.options.placeholder}}Select a date{{/field.options.placeholder}}"
			       class="form-control"
		           data-name="end"
			       {{isCardWrite}}data-provide="datepicker"{{/isCardWrite}}
					data-date-format="{{field.options.format}}{{%field.options.format}}{{:field.options.format}}mm/dd/yyyy{{/field.options.format}}"
					data-date-autoclose="true"
					xxdata-onchange="kanban.fields[{{%field.id}}].onChange(this);"
					value="{{%fieldvalue.content.end}}">
<?php /*
				{{field.options.show_datecount}}
				<span class="datetimeago" data-datetime="{{=field.timeago_dt_gmt}}">
					{{=field.timeago_dt}}
				</span>
				{{/field.options.show_datecount}}
 */ ?>
			</div>
			{{/field.options.is_date_range}}
		</div><!--row-->
	</div>

</script>