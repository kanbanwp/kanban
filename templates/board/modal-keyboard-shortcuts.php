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
						Toggle keyboard shortcuts modal.
					</dd>
					<dt>
						shift + C
					</dt>
					<dd>
						Toggle compact view mode.
					</dd>
					<dt>
						shift + &larr;
					</dt>
					<dd>
						Slide the current board columns to the <i>left</i> (if all columns are not shown).
					</dd>
					<dt>
						shift + &rarr;
					</dt>
					<dd>
						Slide the current board columns to the <i>right</i> (if all columns are not shown).
					</dd>
					<dt>
						shift + P
					</dt>
					<dd>
						Toggle projects modal.
					</dd>
					<dt>
						shift + A
					</dt>
					<dd>
						Toggle showing all columns.
					</dd>
					<dt>
						shift + S
					</dt>
					<dd>
						Jump to the search bar.
					</dd>
					<dt>
						shift + F
					</dt>
					<dd>
						Toggle filter modal.
					</dd>

					<?php echo apply_filters( 'kanban_page_modal_keyboard_shortcuts_after', '' ); ?>

				</dl>
			</div><!-- body -->
		</div>
	</div>
</div>
