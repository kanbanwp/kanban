jQuery( function ( $ ) {
	$( kanban.form_deactivate ).appendTo( 'body' );

	var $a = $( '[data-slug="kanban"] .deactivate a' );

	var url = $a.attr( 'href' ) + '';
	$a.attr( 'href', kanban.url_plugins + '#TB_inline?inlineId=kanban-deactivate-modal&modal=true' ).addClass( 'thickbox' );

	$a.on( 'click', function () {
		setTimeout( function () {
			$( '#TB_ajaxContent' ).css( {
				height: 'auto',
				width: 'auto'
			} );
			$( '#TB_window' ).css( {
				height: 'auto'
			} );

		}, 500 );
	} );



	$( 'body' ).on(
		'click',
		'.kanban-deactivate-remove',
		function () {
			tb_remove();
		}
	);



	$( 'body' ).on(
		'click',
		'.kanban-deactivate-submit',
		function () {
			var data = $( '#kanban-deactivate-form' ).serialize();

			$.ajax( {
				method: "POST",
				url: kanban.url_contact,
				data: data
			} )
			.always( function ( response ) {
				window.location = url;
			} );
		}
	);



	$( '[name="request"]' ).on( 'change', function () {
		$( '.kanban-deactivate-submit' ).text( 'Deactivate' );
		$( this ).closest( 'form' ).find( 'textarea' ).removeAttr( 'name' ).hide();
		$( this ).closest( 'p' ).find( 'textarea' ).attr( 'name', 'message' ).show().focus();
	} );

} );

