<script class="template" type="t/template" data-id="app-modal-keyboard-shortcuts">

	<div id="keyboard-shortcuts-modal" class="modal-dialog modal-lg">
		<div class="modal-content" data-label="<?php _e( 'Keyboard shortcuts', 'kanban' ); ?>">
			<div id="modal-header">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#modal-navbar">
						<span class="sr-only"><?php _e( 'Toggle navigation', 'kanban'); ?></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<span class="navbar-brand visible-xs visible-sm"><?php _e( 'App settings', 'kanban'); ?></span>
				</div>
				<div id="modal-navbar" class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="pull-right">
							<button type="button"
							   onclick="kanban.app.modal.close(this);">
								<span class="visible-xs-inline-block"><?php _e( 'Close this window', 'kanban'); ?></span>
								<i class="ei ei-close hidden-xs"></i>
							</button>
						</li>
					</ul>
				</div><!--/.nav-collapse -->
			</div><!--modal-header -->


			<div class="modal-body">

				<p class="h3">
					<?php echo __( 'Keyboard shortcuts', 'kanban' ); ?>
				</p>

				<dl>
					<?php echo apply_filters( 'kanban_page_modal_keyboard_shortcuts_before', '' ); ?>

					<dt>
						Shift + K
					</dt>
					<dd>
						<?php echo __( 'Toggle keyboard shortcuts modal.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + C
					</dt>
					<dd>
						<?php echo __( 'Toggle compact view mode.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + U
					</dt>
					<dd>
						<?php echo __( 'Toggle full screen mode.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + &larr;
					</dt>
					<dd>
						<?php echo __( 'Slide the current board columns to the left (if all columns are not shown).', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + &rarr;
					</dt>
					<dd>
						<?php echo __( 'Slide the current board columns to the right (if all columns are not shown).', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + P
					</dt>
					<dd>
						<?php echo __( 'Toggle projects modal.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + A
					</dt>
					<dd>
						<?php echo __( 'Toggle showing all columns.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + S
					</dt>
					<dd>
						<?php echo __( 'Jump to the search bar.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + F
					</dt>
					<dd>
						<?php echo __( 'Toggle filter modal.', 'kanban' ); ?>
					</dd>
					<dt>
						Shift + Enter
					</dt>
					<dd>
						<?php echo __( 'Add a new line in the task title field (enter without shift will save your changes).', 'kanban' ); ?>
					</dd>

					<?php echo apply_filters( 'kanban_page_modal_keyboard_shortcuts_after', '' ); ?>

			</div><!--modal-body-->

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->

</script>