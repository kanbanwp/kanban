<script class="template" type="t/template" data-id="footer-menu">

<ul class="dropdown-menu">
	<li>
		<a href="javascript:void(0);"
		   title="shift + C"
		   id="footer-menu-board-view-compact"
		   onclick="kanban.app.viewToggleCompact(this); return false;">
			<?php echo __( 'Compact view', 'kanban' ) ?>
		</a>
	</li>

	<li>
		<a href="javascript:void(0);"
		   title="shift + U"
		   id="footer-menu-board-view-full-screen"
		   onclick="kanban.app.viewToggleFullScreen(this); return false;">
			<?php echo __( 'Full screen', 'kanban' ) ?>
		</a>
	</li>
	<?php /*
					<li>
						<a href="javascript:void(0);">
							<?php echo __( 'Compact View', 'kanban' ) ?>
						</a></li>
 */ ?>
	<li>
		<a href="javascript:void(0);"
		   title="shift + A"
		   id="footer-menu-board-view-toggle-all-lanes"
		   onclick="kanban.app.viewToggleAllLanes(this); return false;">
			<?php echo __( 'Show all lanes', 'kanban' ) ?>
		</a>
	</li>
	<li role="separator" class="divider"></li>
	<li>
		<a href="javascript:void(0); kanban.app.toggleKeyboardShortcutsModal(this);"
		   title="shift + K">
			<?php echo __( 'Keyboard shortcuts', 'kanban' ); ?>
		</a>
	</li>
	{{isSeeBoardModal}}
	<li>
		<a href="javascript:void(0);"
		   title="shift + B"
		   onclick="kanban.app.currentBoardModalShow(); return false;">
			<?php echo __( 'Board settings', 'kanban' ) ?>
		</a>
	</li>
	{{/isSeeBoardModal}}

	<li>
		<a href="javascript:void(0);"
		   title="shift + , (comma)"
		   onclick="kanban.app.modal.show(); return false;">
			<?php echo __( 'App settings', 'kanban' ) ?>
		</a>
	</li>

</ul>

</script>