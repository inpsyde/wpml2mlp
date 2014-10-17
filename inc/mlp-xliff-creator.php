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
		$xliff_file  = $this->get_xlif_file();

		$zip_archive->addFile( $xliff_file, 'export.xliff' );
		$archive_data = $zip_archive->getZippedfile();
		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );
		header( "Content-Disposition: attachment; filename=export.zip" );
		//header("Content-Encoding: gzip");
		header( "Content-Length: " . strlen( $archive_data ) );

		echo $archive_data;
		exit;

	}

	function get_xlif_file() {

		//TODO dsantic 17102014 logic for creating XLIF file goes here
		$data = <<< XML
<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="fr">
	<file id="f1">
		<unit id="u1">
			<my:elem xmlns:my="myNamespaceURI" id="x1">data</my:elem>
			<segment id="s1">
				<source>
					<pc id="1">Hello  World!</pc>
				</source>
				<target>
					<pc id="1">Bonjour le  Monde  !</pc>
				</target>
			</segment>
			<segment id="s2">
				<source>
					<pc id="2">Hello  World!</pc>
				</source>
				<target>
					<pc id="2">Bonjour le  Monde  !</pc>
				</target>
			</segment>
		</unit>
	</file>
</xliff>
XML;

		return $data;
	}

}
