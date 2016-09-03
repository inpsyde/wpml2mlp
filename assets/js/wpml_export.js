jQuery(document).ready(function($){

	if( local_data.current_tab == 'export_wpml' ){
		$( ".wrap" ).hide();
	}

	$( ".wrap.wpml2ml_export" ).show();


	$('.submit').click(function(e){
		e.preventDefault();
		var self = $( this );

		var loaderContainer = $( '<span/>', {
			'class': 'loader-image-container'
		}).insertAfter( self );

		var loader = $( '<img/>', {
			src: local_data.admin_url + '/images/loading.gif',
			'class': 'loader-image'
		}).appendTo( loaderContainer );

		console.log( 'foo' );
		var searchval = $('#s').val(); // get search term

		$.post(
			ajaxurl,
			{
				action : 'add_foobar',
				searchval : searchval
			},
			function( response ) {
				$('#results').empty().append( response );
				loaderContainer.remove();
			}
		);
	});

});
