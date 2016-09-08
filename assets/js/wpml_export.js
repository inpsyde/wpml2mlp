jQuery(document).ready(function($){

	if( local_data.current_tab == 'export_wpml' ){
		$( ".wrap" ).hide();
	}

	$( ".wrap.wpml2ml_export" ).show();


	$('.submit').click(function(e){
		e.preventDefault();
		var self = $( this );
		var parent = $( this ).parent().parent().parent().parent();

		var loaderContainer = $( '<span/>', {
			'class': 'loader-image-container'
		}).insertAfter( self );

		/*var loader = $( '<img/>', {
			src: local_data.admin_url + '/images/loading.gif',
			'class': 'loader-image'
		}).appendTo( loaderContainer );*/

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

		$.get(
			ajaxurl,
			{
				action : 'run_export',
				wpml2mlp : data.wpml2mlp,
				language : data.language,
				timezone : timezone
			},
			function( response ) {

				loaderContainer.remove();

				var response = JSON.parse( response );

				$( '.column-filesize', parent ).text( response.filesize );
				$( '.column-date', parent ).text( response.date );
			}
		);
	});

});
