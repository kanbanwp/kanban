<script class="template" type="t/template" data-id="filter-time">

	<div class="list-group-item field-filter field-filter-time" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Time', 'kanban' ) ?>
			</div><!--col-->
				<div class="col col-sm-4">
					<select class="form-control">
						<option value="<?php _e( '0', 'kanban' ) ?>" {{storedFilter.filterOperator0}}selected="selected"{{/storedFilter.filterOperator0}}><?php _e( '=', 'kanban' ) ?></option>
						<option value="<?php _e( '1', 'kanban' ) ?>" {{storedFilter.filterOperator1}}selected="selected"{{/storedFilter.filterOperator1}}><?php _e( '!=', 'kanban' ) ?></option>					
						<option value="<?php _e( '2', 'kanban' ) ?>" {{storedFilter.filterOperator2}}selected="selected"{{/storedFilter.filterOperator2}}><?php _e( '<', 'kanban' ) ?></option>
						<option value="<?php _e( '3', 'kanban' ) ?>" {{storedFilter.filterOperator3}}selected="selected"{{/storedFilter.filterOperator3}}><?php _e( '<=', 'kanban' ) ?></option>
						<option value="<?php _e( '4', 'kanban' ) ?>" {{storedFilter.filterOperator4}}selected="selected"{{/storedFilter.filterOperator4}}><?php _e( '>', 'kanban' ) ?></option>
						<option value="<?php _e( '5', 'kanban' ) ?>" {{storedFilter.filterOperator5}}selected="selected"{{/storedFilter.filterOperator5}}><?php _e( '>=', 'kanban' ) ?></option>
					</select>
				</div><!--col-->
			<div class="col col-sm-4 form-group form-group-time">
				<!-- <input type="text" class="form-control time-filter-value" value="{{%storedFilter.filterValue}}"> -->
				<div class="clearfix">
					<div class="col xxcol-sm-6 col-hours" data-label="<?php _e('h', 'kanban') ?>">

						<input class="form-control form-control-hours" type="number" step="{{%fieldOptions.step}}"
							min="0"
							onfocus="kanban.fields[{{%field.id}}].onFocus(this, event);"
							onkeydown="kanban.fields[{{%field.id}}].onKeydown(this, event);"
							onblur="kanban.fields[{{%field.id}}].onBlur(this, event);"
							value="{{%storedFilter.filterValue.hours}}"></input>
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
								value="{{%storedFilter.filterValue.estimate}}"></input>
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
				</div>
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>