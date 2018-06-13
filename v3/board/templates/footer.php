<script class="template" type="t/template" data-id="footer">

<div class="container-fluid">
	<div class="navbar-header">

		<button type="button" class="btn btn-default btn-toggle-lane visible-sm-inline visible-xs-inline"
		        data-direction="left"
		        onclick="kanban.app.toggleLane(this);">
			<span class="sr-only"><?php _e( 'Toggle lanes', 'kanban'); ?></span>
			<i class="ei ei-arrow_carrot-left ei-2x"></i>
		</button>

		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#footer-nav">
			<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
			<i class="ei ei-menu ei-2x"></i>
		</button>

		<button type="button" class="btn btn-default btn-toggle-lane visible-sm-inline visible-xs-inline" data-direction="right" onclick="kanban.app.toggleLane(this);">
			<span class="sr-only"><?php _e( 'Toggle lanes', 'kanban'); ?></span>
			<i class="ei ei-arrow_carrot-right ei-2x"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse" id="footer-nav">
		<form class="navbar-form navbar-left">
			<div class="form-group">
				<input type="search" class="form-control" placeholder="<?php _e( 'Search', 'kanban'); ?>" onkeyup="kanban.app.searchCurrentBoard(this)">
			</div>
		</form>

		<ul class="nav navbar-nav">

			<li>
				<a href="javascript:void(0);" class="btn btn-fade btn-empty" data-toggle="modal" data-target="#modal-filters">
					<span class="visible-xs-inline-block"><?php _e( 'Filter', 'kanban'); ?></span>
					<i class="ei ei-adjust-vert ei-2x hidden-xs"></i>
				</a>
			</li>

			<li class="dropup" id="footer-menu">
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
						<a href="javascript:void(0);" id="footer-menu-board-settings-button"
						   title="shift + B"
						   onclick="kanban.app.currentBoardModalShow(); return false;">
							<?php echo __( 'Edit the board', 'kanban' ) ?>
						</a>
					</li>
					{{/isSeeBoardModal}}
					{{isAdmin}}
					<li>
						<a href="javascript:void(0);"
						   title="shift + , (comma)"
						   onclick="kanban.app.modal.show(); return false;">
							<?php echo __( 'Edit the app', 'kanban' ) ?>
						</a>
					</li>
					{{/isAdmin}}
				</ul>
				<a href="javascript:void(0);" class="btn btn-empty btn-fade" data-toggle="dropdown">
					<span class="visible-xs-inline-block"><?php _e( 'Options', 'kanban'); ?></span>
					<i class="ei ei-cog ei-2x hidden-xs"></i>
				</a>
			</li>

		</ul>
	</div><!-- /.navbar-collapse -->
</div><!-- /.container-fluid -->

<i class="ei ei-loading ei-2x" id="app-ajax-loading" style="display: none;"></i>

</script>