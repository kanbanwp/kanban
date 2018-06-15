<script class="template" type="t/template" data-id="header-board-tab">

	<li class="{{isActive}}active{{/isActive}}">
		<a href="#board-{{%board.id}}"
		   id="header-board-tab-{{%board.id}}"
		   data-id="{{%board.id}}"
		   data-toggle="tab"
		   onclick="kanban.boards[{{%board.id}}].tabToBoard(this); return false;">
			{{board.label}}{{=board.label}}{{:board.label}}<?php _e( 'New Board', 'kanban'); ?>{{/board.label}}
		</a>
	</li>

</script>