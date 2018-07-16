<p><?php echo sprintf(
	__( 'You\'ve been added to the "%s" field on card #%d on board "%s"', 'kanban'),
	$field_label,
	$card_id,
	$board_label); ?>
	<?php _e( ':', 'kanban') ?>
</p>

<div><?php echo $content ?></div>

<hr>

<p><?php _e('Follow this link to see the card:', 'kanban') ?>
	<a href="<?php echo $card_url ?>" target="_blank"><?php echo $card_url ?></a>
</p>