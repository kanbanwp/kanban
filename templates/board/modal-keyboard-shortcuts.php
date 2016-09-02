<div class="modal fade" id="modal-keyboard-shortcuts">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="close" data-dismiss="modal">&times;</button>

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

				</dl>
			</div><!-- body -->
		</div>
	</div>
</div>
