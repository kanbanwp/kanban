<script class="template" type="t/template" data-id="filter-todo">

	<div class="list-group-item field-filter" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Todo', 'kanban' ) ?>
			</div><!--col-->
			<div class="col col-sm-4">
				<select class="form-control">									
					<option value="<?php _e( '6', 'kanban' ) ?>" {{storedFilter.filterOperator6}}selected="selected"{{/storedFilter.filterOperator6}}><?php _e( 'includes', 'kanban' ) ?></option>
					<option value="<?php _e( '7', 'kanban' ) ?>" {{storedFilter.filterOperator7}}selected="selected"{{/storedFilter.filterOperator7}}><?php _e( 'does not include', 'kanban' ) ?></option>
				</select>
			</div><!--col-->
			<div class="col col-sm-4">
				<input type="text" class="form-control" value="{{%storedFilter.filterValue}}">
				<p>E.g. todo1, todo2</p>
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>