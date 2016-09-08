<?php

/**
 * Class Wpml2mlp_Xliff_Creator
 */
class Wpml2mlp_Xliff_Creator {

	/**
	 * @var $contentForExport
	 */
	private $contentForExport;

	/**
	 * @param $property
	 *
	 * @return property value if property exists.
	 */
	public function __get( $property ) {

		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}

	/**
	 * Sets property value if property exists.
	 *
	 * @param $property
	 * @param $value
	 */
	public function __set( $property, $value ) {

		if ( property_exists( $this, $property ) ) {
			$this->$property = $value;
		}
	}

	/**
	 * Setup xliff creator.
	 */
	function setup() {

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

	/**
	 * Triggers xliff export.
	 */
	function trigger_export() {

		//$data  = base64_encode( serialize( $data ) );
		$nonce = wp_create_nonce( 'xliff-export' );

		?>

		<script type="text/javascript">
			var xliff_export_nonce = "<?php echo $nonce; ?>";
			addLoadEvent( function() {
				window.location = "<?php echo htmlentities($_SERVER['REQUEST_URI']) ?>&mlp_xliff_action=download&nonce=" + xliff_export_nonce;
			} );

		</script>

		<?php
	}

	/**
	 * Runs xliff export.
	 */
	function  do_xliff_export() {

		$data = $this->contentForExport;
		//$data = unserialize( base64_decode( $data ) );

		$zip_archive = new Wpml2mlp_ZipCreator();

		if ( is_array( $data ) && count( $data ) > 0 ) {
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

	/**
	 * Runs storing xliff data.
	 */
	function store_wxr_export() {

		$data = $this->contentForExport;

		if ( is_array( $data ) && count( $data ) > 0 ) {

			foreach ( $data as $locale => $posts ) {

				$wxr[ $locale ] = $this->get_wxr_file( $locale, $posts );

				foreach( $wxr[ $locale ] as $wxr_file ) {

					$wxr[ 'filesize' ] = size_format( filesize( $wxr_file ), 2 );
					$wxr[ 'date' ]  = date( 'm.d.y H:i', filemtime( $wxr_file ) );

				}
				#buddy take a break, its hard work but now we have a wxr export file created :)
				sleep(2);

			}

		}

		print_r( json_encode( $wxr ) );
		die();
	}

	/**
	 * Creates xliff file from WPML2MLP_Translations object.
	 *
	 * @param WPML2MLP_Translations $data
	 *
	 * @return string
	 */
	function get_xlif_file( WPML2MLP_Translations $data ) {


		$new_line = "\n";

		$xliff_file = '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="' . $data->source_language . '" trgLang="' . $data->destination_language . '" translations="' . count( $data->data ) . '">' . $new_line;
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


	function get_wxr_file( $locale, $posts ) {

		$wxr = new Wpml_Wxr_Export( $locale, $posts );

		$wxr_file = $wxr->get_wxr();

		return $wxr_file;

	}

}
