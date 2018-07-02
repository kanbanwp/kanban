<script class="template" type="t/template" data-id="filter-date">

	<div class="list-group-item field-filter" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Date', 'kanban' ) ?>
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
			<div class="col col-sm-4">
				<input type="text" class="form-control date-filter-value" value="{{%storedFilter.filterValue}}">
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>