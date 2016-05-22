<div class="modal fade" id="modal-boards">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<button type="button" class="close" data-dismiss="modal">&times;</button>

				<p class="h3">
				<?php echo __( 'Boards', 'kanban' ); ?>
				</p>

				<div class="list-group">
<?php foreach ($boards as $board) : ?>
					<a href="#board-<?php echo $board->id ?>" data-toggle="tab" class="list-group-item" data-dismiss="modal"><?php echo $board->title ?></a>
<?php endforeach // boards ?>
				</div><!-- list-group -->
			</div><!-- body -->
		</div>
	</div>
</div>
