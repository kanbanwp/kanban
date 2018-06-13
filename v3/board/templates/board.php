<script class="template" type="t/template" data-id="board">

	<div class="board {{%view}}" data-id="{{%board.id}}" id="board-{{%board.id}}">
		<div class="row row-board"
		     data-offset="{{%ui.lane_show_percent}}"
		     data-left="-{{%ui.lane_show_percent}}"
		     style="margin-left: -{{%ui.lane_show_percent}}%; width: {{%ui.lane_width}}%;">
			{{=lanes}}
		</div>

	</div><!--board-->

</script>