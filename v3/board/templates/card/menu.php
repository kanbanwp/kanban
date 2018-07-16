<script class="template" type="t/template" data-id="card-menu">

	<div class="popover card-actions"
	     onmouseenter="kanban.cards[{{%card.id}}].menuShow(this);"
	     onmouseleave="kanban.cards[{{%card.id}}].menuHideDelay(this);"
	     id="popover-card-edit-menu-{{%card.id}}">
		<div class="arrow"></div>
		<div class="btn-group-vertical card-edit-menu">
			<button type="button" class="btn btn-default"
			        onclick="kanban.cards[{{%card.id}}].menuHide(this); kanban.cards[{{%card.id}}].currentUserToggleFollow(this); return false;">

				{{isFollowed}}
				<i class="is-followed ei ei-2x ei-star"></i>
				{{:isFollowed}}
				<i class="is-not-followed ei ei-2x ei-star_alt"></i>
				{{/isFollowed}}
			</button>

			<button type="button" class="btn btn-default"
			        onclick="kanban.cards[{{%card.id}}].menuHide(this); kanban.cards[{{%card.id}}].copy(this); return false;">
				<i class="ei ei-2x ei-documents_alt"></i>
			</button>

			<button type="button" class="btn btn-default"
			        onclick="kanban.cards[{{%card.id}}].menuHide(this); kanban.cards[{{%card.id}}].delete(this);">
				<i class="ei ei-2x ei-trash_alt"></i>
			</button>
		</div>
	</div>



</script>