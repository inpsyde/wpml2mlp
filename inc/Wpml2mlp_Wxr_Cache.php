<?php

/**
 * Class to dynamically create and handle wxr files as gz
 */
class Wpml2mlp_Wxr_Cache {

	/**
	 * @var array hold generatet wxr files
	 */
	public $wxr = array();

	/**
	 * @var int
	 */
	public $oldOffset = 0;

	/**
	 * @return array retruns a stack of generatet translation wxr files
	 */
	public function get_wxr_stack(){

		return $this->wxr;

	}

	/**
	 * Cleat the wxr stack and delete generatet wxr files
	 */
	public function clear_wxr_stack(){

		if( count( $this->wxr ) > 0){

			foreach( $this->wxr as $file ){

				unlink( $file );

			}

			$this->wxr = array();
		}

	}

	/**
	 * @return array retruns a stack of generatet translation wxr files
	 */
	public function unlink_wxr( $wxr_cache_name ){

		$wxr_filepath = apply_filters( 'wpml2mlp_wxr_filepath', WP_CONTENT_DIR . '/uploads/wpml2mlp/' );
		$wxr_filename = $wxr_filepath . apply_filters( 'wpml2mlp_wxr_filename', $wxr_cache_name );

		if ( file_exists( $wxr_filename ) ) {

			unlink( $wxr_filename );

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
	public function write( $wxr_data, $wxr_cache_name, $gz = false ) {

		$wxr_filepath = apply_filters( 'wpml2mlp_wxr_filepath', WP_CONTENT_DIR . '/uploads/wpml2mlp/' );
		$wxr_filename = $wxr_filepath . apply_filters( 'wpml2mlp_wxr_filename', $wxr_cache_name );

		if ( ! file_exists( $wxr_filepath ) ) {
			mkdir( $wxr_filepath, 0777, TRUE );
		}

		file_put_contents( $wxr_filename, $wxr_data, FILE_APPEND | LOCK_EX );
		$this->wxr[ $wxr_cache_name ] = $wxr_filename;

		/**
		 * GZ file compression
		 *
		 * The wxr files are defauldy gz compressed.
		 * Compression can turn off by a Filter "wpml2mlp_wxr_crompress"
		 *
		 * Example:
		 * add_filter( 'wpml2mlp_wxr_crompress', function( retrun false; ) );
		 *
		 */
		if ( $gz === TRUE ) {

			$this->wxr[ $wxr_cache_name ] = $this->gzCompressFile( $wxr_filename );

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