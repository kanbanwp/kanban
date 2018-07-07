<script class="template" type="t/template" data-id="filter-title">

	<div class="list-group-item field-filter" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Title', 'kanban' ) ?>
			</div><!--col-->
			<div class="col col-sm-4">
				<select class="form-control">
					<option value="<?php _e( '0', 'kanban' ) ?>" {{storedFilter.filterOperator0}}selected="selected"{{/storedFilter.filterOperator0}}><?php _e( '=', 'kanban' ) ?></option>
					<option value="<?php _e( '1', 'kanban' ) ?>" {{storedFilter.filterOperator1}}selected="selected"{{/storedFilter.filterOperator1}}><?php _e( '!=', 'kanban' ) ?></option>					
					<option value="<?php _e( '6', 'kanban' ) ?>" {{storedFilter.filterOperator6}}selected="selected"{{/storedFilter.filterOperator6}}><?php _e( 'includes', 'kanban' ) ?></option>
					<option value="<?php _e( '7', 'kanban' ) ?>" {{storedFilter.filterOperator7}}selected="selected"{{/storedFilter.filterOperator7}}><?php _e( 'does not include', 'kanban' ) ?></option>
				</select>
			</div><!--col-->
			<div class="col col-sm-4">
				<input type="text" class="form-control" value="{{%storedFilter.filterValue}}">
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>