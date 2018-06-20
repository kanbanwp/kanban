<script class="template" type="t/template" data-id="card-menu">

	<div class="popover card-actions"
	     onmouseenter="kanban.cards[{{%card.id}}].menuShow(this);"
	     onmouseleave="kanban.cards[{{%card.id}}].menuHideDelay(this);"
	     id="popover-card-edit-menu-{{%card.id}}">
		<div class="arrow"></div>
		<div class="btn-group-vertical card-edit-menu">
			<button type="button" class="btn btn-default btn-sm"
			        onclick="kanban.cards[{{%card.id}}].menuHide(this); kanban.cards[{{%card.id}}].copy(this); return false;">
				<?php _e( 'Copy', 'kanban' ); ?>
			</button>

			<button type="button" class="btn btn-default btn-sm"
			        onclick="kanban.cards[{{%card.id}}].menuHide(this); kanban.cards[{{%card.id}}].delete(this);">
				<?php _e( 'Archive', 'kanban' ); ?>
				<i class="ei ei-loading show-on-loading" style="color: {{%lane.color}}"></i>
			</button>
		</div>
	</div>



</script>