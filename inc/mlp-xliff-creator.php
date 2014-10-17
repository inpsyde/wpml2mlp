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
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
   <file original="1-6512bd43d9caa6e02c990b0a82652dca" source-language="en" target-language="bs" datatype="plaintext">
      <header />
      <body>
         <trans-unit resname="title" restype="string" datatype="html" id="title">
            <source><![CDATA[Hello world!]]></source>
            <target><![CDATA[Hello world from Bosnia]]></target>
         </trans-unit>
         <trans-unit resname="body" restype="string" datatype="html" id="body">
            <source><![CDATA[Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!]]></source>
            <target><![CDATA[Welcome to WordPressfrom Bosnia]]></target>
         </trans-unit>
         <trans-unit resname="categories" restype="string" datatype="html" id="categories">
            <source><![CDATA[Uncategorized]]></source>
            <target><![CDATA[Uncategorized]]></target>
         </trans-unit>
      </body>
   </file>
</xliff>
XML;

		return $data;
	}

}
