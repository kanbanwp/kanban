<script class="template" type="t/template" data-id="filter-colorpicker">

	<div class="list-group-item field-filter field-filter-colorpicker" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Colorpicker', 'kanban' ) ?>
			</div><!--col-->
			<div class="col col-sm-4">
				<select class="form-control">
					<option value="<?php _e( '0', 'kanban' ) ?>" {{storedFilter.filterOperator0}}selected="selected"{{/storedFilter.filterOperator0}}><?php _e( '=', 'kanban' ) ?></option>
					<option value="<?php _e( '1', 'kanban' ) ?>" {{storedFilter.filterOperator1}}selected="selected"{{/storedFilter.filterOperator1}}><?php _e( '!=', 'kanban' ) ?></option>					
				</select>
			</div><!--col-->
			<div class="col col-sm-4 form-group-colorpicker">				
				<div class="dropdown">
					<button type="button" class="btn btn-empty btn-color"							
							data-toggle="dropdown"
							data-color="{{%storedFilter.filterValue}}"
							onclick="kanban.fields[{{%field.id}}].onClick(this);"							
							style="background:
							{{%storedFilter.filterValue}};">
					</button>					
					<div class="dropdown-menu">
					</div>					
				</div><!--dropdown-->
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>