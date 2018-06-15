<script class="template" type="t/template" data-id="footer-menu">




		<ul class="dropdown-menu">
			<li>
				<a href="javascript:void(0);">
					<?php echo __( 'Full Screen', 'kanban' ) ?>
				</a></li>
			<li>
				<a href="javascript:void(0);">
					<?php echo __( 'Compact View', 'kanban' ) ?>
				</a></li>
			<li>
				<a href="javascript:void(0);"
				   id="footer-menu-board-view-toggle-all-lanes"
				   class="{{viewAllLanes}}active{{/viewAllLanes}}"
				   onclick="kanban.app.viewToggleAllLanes(this); return false;">
					<?php echo __( 'Show all lanes', 'kanban' ) ?>
				</a>
			</li>
			<li role="separator" class="divider"></li>
			<li>
				<a href="javascript:void(0);">
					<?php echo __( 'Keyboard shortcuts', 'kanban' ); ?>
				</a>
			</li>
			{{isSeeBoardModal}}
			<li>
				<a href="javascript:void(0);"
				   onclick="kanban.app.currentBoardModalShow(); return false;">
					<?php echo __( 'Edit the board', 'kanban' ) ?>
				</a>
			</li>
			{{/isSeeBoardModal}}
			{{isAdmin}}
			<li>
				<a href="javascript:void(0);" onclick="kanban.app.modal.show(); return false;">
					<?php echo __( 'Edit the app', 'kanban' ) ?>
				</a>
			</li>
			{{/isAdmin}}
		</ul>
		<a href="javascript:void(0);" class="btn btn-empty btn-fade" data-toggle="dropdown">
			<span class="visible-xs-inline-block"><?php _e( 'Settings', 'kanban'); ?></span>
			<i class="ei ei-cog ei-2x hidden-xs"></i>
		</a>



</script>