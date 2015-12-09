<?php

/**
 * Class to dynamically create and handle xliff files as gz
 */
class Wpml2mlp_Xliff_Cache {

	/**
	 * @var array hold generatet xliff files
	 */
	public $xliff = array();

	/**
	 * @var int
	 */
	public $oldOffset = 0;

	/**
	 * @return array retruns a stack of generatet translation xliff files
	 */
	public function get_xlifff_stack(){

		return $this->xliff;

	}

	/**
	 * Cleat the xliff stack and delete generatet xliff files
	 */
	public function clear_xlifff_stack(){

		if( count( $this->xliff ) > 0){

			foreach( $this->xliff as $file ){

				unlink( $file );

			}

			$this->xliff = array();
		}

	}


	/**
	 * Function to add file(s) to the specified directory in the archive
	 *
	 * @param string $directoryName
	 * @param string $data
	 *
	 * @return void
	 * @access public
	 */
	public function add( $xliff_data, $xliff_cache_name ) {

		$xliff_filepath = apply_filters( 'wpml2mlp_xliff_filepath', WP_CONTENT_DIR . '/uploads/' );
		$xliff_filename = $xliff_filepath . apply_filters( 'wpml2mlp_xliff_filename', $xliff_cache_name );

		if ( ! file_exists( $xliff_filepath ) ) {
			mkdir( $xliff_filepath, 0777, TRUE );
		}

		/**
		 * GZ file compression
		 *
		 * The xliff files are defauldy gz compressed.
		 * Compression can turn off by a Filter "wpml2mlp_xliff_crompress"
		 *
		 * Example:
		 * add_filter( 'wpml2mlp_xliff_crompress', function( retrun false; ) );
		 *
		 */
		if ( apply_filters( 'wpml2mlp_xliff_crompress', TRUE ) === TRUE ) {

			file_put_contents( $xliff_filename, $xliff_data );
			$this->xliff[] = $this->gzCompressFile( $xliff_filename );

		} else {

			if ( file_exists( $xliff_filename ) ) {

				unlink( $xliff_filename );

			}

			file_put_contents( $xliff_filename, $xliff_data );
			$this->xliff[] = $xliff_filename;

		}

	}

	/**
	 * GZIPs a file on disk (appending .gz to the name)
	 *
	 * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
	 * Based on function by Kioob at:
	 * http://www.php.net/manual/en/function.gzwrite.php#34955
	 *
	 * @param string  $source Path to file that should be compressed
	 * @param integer $level  GZIP compression level (default: 9)
	 *
	 * @return string New filename (with .gz appended) if success, or false if operation fails
	 */
	private function gzCompressFile( $source, $level = 9 ) {

		$dest  = $source . '.gz';
		$mode  = 'wb' . $level;

		if ( file_exists( $dest ) ) {

			unlink( $dest );

		}

		if ( $fp_out = gzopen( $dest, $mode ) ) {

			if ( $fp_in = fopen( $source, 'rb' ) ) {

				while ( ! feof( $fp_in ) ) {
					gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
				}

				fclose( $fp_in );

			}

			gzclose( $fp_out );

			unlink( $source );

			return $dest;

		}

		return FALSE;

	}

}

?>