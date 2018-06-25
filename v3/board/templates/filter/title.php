<script class="template" type="t/template" data-id="filter-title">

	<div class="list-group-item field-filter" data-id="{{%fieldId}}">
		<div class="row">
			<div class="col col-sm-4">
				<?php _e( 'Title', 'kanban' ) ?>
			</div><!--col-->
			<div class="col col-sm-4">
				<select class="form-control">
					<option value="<?php _e( 'includes', 'kanban' ) ?>"><?php _e( 'includes', 'kanban' ) ?></option>
					<option value="<?php _e( 'does not include', 'kanban' ) ?>"><?php _e( 'does not include', 'kanban' ) ?></option>
				</select>
			</div><!--col-->
			<div class="col col-sm-4">
				<input type="text" class="form-control" value="{{%storedValue}}">
			</div><!--col-->
		</div><!--row-->
	</div><!--list-group-item-->

</script>