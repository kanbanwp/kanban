<script class="template" type="t/template" data-id="lane">

	<div class="col col-sm-3 lane {{active}}active{{/active}}"
	     style="width: {{%board.ui.lane_percent}}%"
	     data-id="{{%lane.id}}"
	     id="lane-{{%lane.id}}">
		<div class="lane-wrapper-header">
			<div class="lane-header" style="width: {{%board.ui.lane_header_percent}}%;">
<?php /*
				<div class="lane-header-menu">
					<div class="btn btn-xs btn-fade lane-move-handle">
						<i class="ei ei-menu" style="color: {{%lane.options.color}}"></i>
					</div>

					<div class="btn btn-xs btn-fade lane-edit" data-toggle="modal" data-target="#modal-lane">
						<i class="ei ei-pencil" style="color: {{%lane.options.color}}"></i>
					</div>
				</div><!--menu-->
 */ ?>

				<span class="lane-label"
					      style="border-color: {{%lane.options.color}};">
					{{lane.label}}{{=lane.label}}{{:lane.label}}<?php _e( 'New lane', 'kanban'); ?>{{/lane.label}}
				</span>
				<sup class="lane-header-card-count">{{=cardCount}}</sup>
{{isCreateCard}}
				<button class="btn btn-xs btn-empty lane-add-card" onclick="kanban.lanes[{{%lane.id}}].cardAdd(this);">
					<i class="ei ei-plus hide-on-loading" style="color: {{%lane.options.color}}"></i>
					<i class="ei ei-loading show-on-loading" style="color: {{%lane.options.color}}"></i>
				</button>
{{/isCreateCard}}
			</div><!--header-->
		</div><!--wrapper-header-->

		<div class="lane-body wrapper-cards highlight" data-lane-id="{{%lane.id}}">
			{{=cards}}
		</div>


	</div><!--col-->

	{{sidebar}}
	<?php // MUST BE SPAN TO ALLOW ODD LANE BACKGROUND COLORS ?>
	<span class="sidebar-toggle sidebar-toggle-{{%sidebar}} hidden-sm hidden-xs"
	      id="sidebar-toggle-{{%sidebar}}"
	      data-direction="{{%sidebar}}"
	     onclick="kanban.boards[{{%lane.board_id}}].sidebarToggle(this);">
		<i class="ei ei-arrow_carrot-{{%sidebar}}" style="color: {{%lane.options.color}}"></i>
	</span>
	<?php // MUST BE SPAN TO ALLOW ODD LANE BACKGROUND COLORS ?>
	{{/sidebar}}


</script>