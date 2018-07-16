<p><?php echo sprintf(
		__('Field "%s" on card "%s" has been updated:', 'kanban'),
		$field_label,
		$card_id
	) ?></p>

<div style="background: #F6F6F6; color: black; padding: 15px 10px;">
	<?php echo $content ?>
</div>

<hr>

<p><?php _e('Follow this link to see the card:', 'kanban') ?>
	<a href="<?php echo $card_url ?>" target="_blank"><?php echo $card_url ?></a>
</p>