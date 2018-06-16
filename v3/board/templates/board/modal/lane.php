<script class="template" type="t/template" data-id="board-modal-lane">

	<div class="panel panel-default" data-lane-id="{{%lane.id}}">
		<div class="panel-heading">
			<a href="javascript:void(0);" class="btn btn-xs btn-empty pull-right ei ei-menu board-modal-lane-handle">
			</a>

			<a class="h4 panel-title"
			   data-toggle="collapse"
			   data-parent="#board-modal-lanes-accordion"
			   href="#board-modal-lane-{{%lane.id}}">
				{{lane.label}}{{=lane.label}}{{:lane.label}}<span class="text-muted"><?php _e( 'New lane', 'kanban'); ?></span>{{/lane.label}}
			</a>
		</div>
		<div id="board-modal-lane-{{%lane.id}}" class="panel-collapse collapse {{open}}in{{/open}}">
			<div class="panel-body">
				<div class="wrapper-form-group row">
					<div class="form-group form-group-title col col-sm-12">
						<label><?php _e( 'Lane title:', 'kanban'); ?></label>
						<input type="text"
						       class="form-control"
						       onfocus="kanban.lanes[{{%lane.id}}].titleOnfocus(this);"
						       onkeydown="kanban.lanes[{{%lane.id}}].titleOnkeydown(this, event);"
						       onblur="kanban.lanes[{{%lane.id}}].titleOnblur(this);"
						       data-name="label"
						       autocomplete="off"
						       maxlength="24"
						       placeholder="<?php _e( 'Lane title', 'kanban'); ?>"
						       value="{{%lane.label}}">
					</div><!--form-group-text -->

					<div class="form-group form-group-colorpicker col col-sm-12">
						<label><?php _e( 'Color:', 'kanban'); ?></label>

						<div class="dropdown">
							<button type="button"
							        class="btn btn-empty btn-color"
							        style="background: {{%optionsLane.color}}"
							        onclick="kanban.lanes[{{%lane.id}}].colorOnclick(this);"
							        data-toggle="dropdown">
							</button>

							<div class="dropdown-menu">
							</div><!--dropdown-menu-->
						</div><!--dropdown-->
					</div><!--form-group -->
				</div><!--wrapper-form-group -->

				<p class="text-center">
					<a data-toggle="collapse" class="btn btn-default btn-block" href="#board-modal-lane-{{%lane.id}}-delete">
						<?php _e( 'More options', 'kanban'); ?>
						<i class="ei ei-arrow_carrot-down"></i>
					</a>
				</p>

				<div class="collapse" id="board-modal-lane-{{%lane.id}}-delete">
					<p>
						<button type="button" class="btn btn-danger"
						        onclick="kanban.boards[{{%lane.board_id}}].modal.laneDelete(this);">
							<?php _e( 'Delete', 'kanban'); ?>
						</button>
					</p>
				</div><!--collapse-->

			</div><!--panel-body-->
		</div><!--panel-collapse -->
	</div><!--panel-->

</script>