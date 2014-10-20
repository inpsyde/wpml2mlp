<?php

class MLP_Xliff_Creator {

	function __construct() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		if ( is_admin() ) {
			add_action(
				'WPML2MLP_xliff_export',
				array( $this, 'trigger_export' )
			);

		}

		if ( isset( $_GET[ 'mlp_xliff_action' ] ) && $_GET[ 'mlp_xliff_action' ] == 'download'
		     && $_GET[ 'nonce' ] = wp_create_nonce(
				'xliff-export'
			)
		) {
			$this->do_xliff_export();
		}
	}

	function trigger_export( $data ) {

		$data  = base64_encode( serialize( $data ) );
		$nonce = wp_create_nonce( 'xliff-export' );

		?>

		<script type="text/javascript">

			var xliff_export_data = "<?php echo $data; ?>";
			var xliff_export_nonce = "<?php echo $nonce; ?>";
			addLoadEvent( function() {
				window.location = "<?php echo htmlentities($_SERVER['REQUEST_URI']) ?>&mlp_xliff_action=download&xliff_export_data=" + xliff_export_data + "&nonce=" + xliff_export_nonce;
			} );

		</script>

	<?php
	}

	function do_xliff_export() {

		$data = $_GET[ 'xliff_export_data' ];
		$data = unserialize( base64_decode( $data ) );

		$zip_archive = new ZipCreator();

		if ( is_array( $data ) ) {
			foreach ( $data as $lng ) {
				$xliff_file = $this->get_xlif_file( $lng );
				$filename   = $lng->source_language . '_' . $lng->destination_language;
				$zip_archive->addFile( $xliff_file, 'translation_' . $filename . '.xliff' );
			}
			$archive_data = $zip_archive->getZippedfile();
			header( "Content-Type: application/force-download" );
			header( "Content-Type: application/octet-stream" );
			header( "Content-Type: application/download" );
			header( "Content-Disposition: attachment; filename=wpml2mlp_xliff_export.zip" );
			//header("Content-Encoding: gzip");
			header( "Content-Length: " . strlen( $archive_data ) );

			echo $archive_data;
		}

		exit;

	}

	function get_xlif_file( MLP_Translations $data ) {

		$new_line   = "\n";
		$xliff_file = '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="' . $data->source_language . '" trgLang="' . $data->destination_language . '">' . $new_line;
		$xliff_file .= '<file id="f1">' . $new_line;
		$i = 0;
		foreach ( $data->data as $segment ) {
			$xliff_file .= '<unit id="u' . $i . '">' . $new_line;
			$xliff_file .= '<segment id="' . $segment->original_id . '">' . $new_line;
			$xliff_file .= '<source>' . $new_line;
			$xliff_file .= '<pc id="' . $segment->post_id . '">' . $segment->source . '</pc>' . $new_line;
			$xliff_file .= '</source>' . $new_line;
			$xliff_file .= '<target>' . $new_line;
			$xliff_file .= '<pc id="' . $segment->post_id . '">' . $segment->target . '</pc>' . $new_line;
			$xliff_file .= '</target>' . $new_line;
			$xliff_file .= '</segment>' . $new_line;
			$xliff_file .= '</unit>' . $new_line;
			$i ++;
		}

		$xliff_file .= '</file>' . $new_line;
		$xliff_file .= '</xliff>';

		return $xliff_file;

	}

}
