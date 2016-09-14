jQuery(document).ready(function($){

	if( local_data.current_tab == 'export_wpml' ){
		$( ".wrap" ).hide();
	}

	$( ".wrap.wpml2ml_export" ).show();

	// Bulkaction
	$('.button.action').click(function(e){

		var seleced = [];

		var checkboxes = $( 'tbody .check-column input' );

		checkboxes.each( function( i ) {

			if ( $( this ).is( ':checked' ) == true ) {
				seleced.push( $( this ).val() );
			}

		} );

		$( '#lang_' + seleced[0] ).trigger( "click" );


	});

	//Single export
	$('.submit').click(function(e){
		e.preventDefault();
		var self = $( this );
		var parent = $( this ).parent().parent().parent().parent();

		$( '.check-column input', parent ).prop( "checked", false );

		var loaderContainer = $( '<span/>', {
			'class': 'loader-image-container'
		}).insertAfter( self );

		var data = JSON.parse( self.attr( 'data-export' ) );

		$( '.column-filesize', parent ).empty();
		$( '.column-date', parent ).empty();

		$( '<img/>', {
			src: local_data.admin_url + '/images/loading.gif',
			'class': 'loader-image'
		}).appendTo( $( '.column-date', parent ) );

		$( '<img/>', {
			src: local_data.admin_url + '/images/loading.gif',
			'class': 'loader-image'
		}).appendTo( $( '.column-filesize', parent ) );

		var d = new Date();
		var timezone = d.getTimezoneOffset();

		$.ajax({
			async: true,
			url: ajaxurl,
			type: "GET",
			data: {
				action : 'run_export',
				wpml2mlp : data.wpml2mlp,
				language : data.language,
				timezone : timezone
			},
			success: function ( response ){

				loaderContainer.remove();

				var response = JSON.parse( response );

				$( '.column-filesize', parent ).text( response.filesize );
				$( '.column-date', parent ).text( response.date );

				$('.button.action' ).trigger( 'click' );

			}
		});

	});

});
