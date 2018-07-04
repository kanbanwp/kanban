<p><?php echo sprintf(__('Card "%s" on the "%s" Kanban board has been updated:', 'kanban'), $card_name, $board_name) ?></p>

<div><?php echo $content ?></div>

<hr>

<p><?php _e('Follow this link to see the card:', 'kanban') ?>
	<a href="<?php echo $card_url ?>" target="_blank"><?php echo $card_url ?></a>
</p>