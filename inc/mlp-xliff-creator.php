<?php

class MLP_Xliff_Creator {

	function __construct() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		if ( is_admin() ) {
			add_action(
				'WPML2MLP_xliff_export',
				array( $this, 'do_xliff_export' )
			);

		}
	}

	function do_xliff_export( $data ) {

		var_dump( $data );
	}

}
