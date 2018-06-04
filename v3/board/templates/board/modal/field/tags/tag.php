<script class="template" type="t/template" data-id="board-modal-field-tags-tag">

	<div class="input-group board-modal-field-tag" data-tag-id="{{%tag.id}}">
		<input type="text"
		       class="form-control"
		       data-tag-id="{{%tag.id}}"
		       value="{{%tag.content}}"
		       onfocus="kanban.fields[{{%field_id}}].optionsTagOnfocus(this);"
		       onkeyup="kanban.fields[{{%field_id}}].optionsTagOnkeydown(this, event);"
		       onblur="kanban.fields[{{%field_id}}].optionsTagOnblur(this);">
		<span class="input-group-btn">
        <button type="button"
                class="btn btn-sm btn-danger"
                data-tag-id="{{%tag.id}}"
                onclick="kanban.fields[{{%field_id}}].optionsDeleteTag(this);">
				&times;
		</button>
		</span>
	</div>

</script>