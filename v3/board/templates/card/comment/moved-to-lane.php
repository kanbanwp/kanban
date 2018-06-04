<script class="template" type="t/template" data-id="card-comment-moved-to-lane">

	<?php echo sprintf( __( 'Moved the card to "%s"', 'kanban' ), '{{=newLane}}' ) ?>

	{{prevLane}}
	<?php echo sprintf( __( ' (previously "%s")', 'kanban' ), '{{=prevLane}}' ) ?>
	{{/prevLane}}

</script>