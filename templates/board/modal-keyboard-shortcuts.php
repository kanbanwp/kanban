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
						shift + K
					</dt>
					<dd>
						<?php echo __( 'Toggle keyboard shortcuts modal.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + C
					</dt>
					<dd>
						<?php echo __( 'Toggle compact view mode.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + &larr;
					</dt>
					<dd>
						<?php echo __( 'Slide the current board columns to the left (if all columns are not shown).', 'kanban' ); ?>
					</dd>
					<dt>
						shift + &rarr;
					</dt>
					<dd>
						<?php echo __( 'Slide the current board columns to the right (if all columns are not shown).', 'kanban' ); ?>
					</dd>
					<dt>
						shift + P
					</dt>
					<dd>
						<?php echo __( 'Toggle projects modal.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + A
					</dt>
					<dd>
						<?php echo __( 'Toggle showing all columns.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + S
					</dt>
					<dd>
						<?php echo __( 'Jump to the search bar.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + F
					</dt>
					<dd>
						<?php echo __( 'Toggle filter modal.', 'kanban' ); ?>
					</dd>
					<dt>
						shift + enter
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
