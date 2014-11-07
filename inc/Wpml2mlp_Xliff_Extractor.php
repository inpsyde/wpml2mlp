<?php

class Wpml2mlp_Xliff_Extractor {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
	}

	public function  check_and_get_xliff_zip( $zip_file_stream ) {

		if ( $zip_file_stream[ 'error' ] == UPLOAD_ERR_OK
			&& is_uploaded_file( $zip_file_stream[ 'tmp_name' ] )
			&& self::is_file_with_extension( $zip_file_stream[ 'name' ], 'zip' )
		) {   // checks that file is uploaded and that is zip

			$source = $zip_file_stream[ "tmp_name" ];

			return $source;
		}

		return FALSE;
	}

	public function extract( $zip_file ) {

		$posts = array();
		if ( ! $zip_file ) {
			return $posts;
		}
		$z      = new Zip;
		$result = $z->Extract( $zip_file, "." );

		if ( ! $result ) {
			$posts;
		}
		foreach ( $result as $key => $value ) {
			if ( ! self::is_file_with_extension( $key, 'xliff' ) ) {
				continue;
			}
			$xliff = self::get_xliff( $value );

			$translation_item = self::get_translation_item( $xliff->children() );

			if ( ! self::is_valid( $translation_item ) ) {
				continue;
			}

			$post = get_post( $translation_item[ 'id' ] );
			if ( ! $post ) {
				continue;
			}

			if ( $post->post_title != $translation_item[ 'title' ] ) {
				$post->post_title = $translation_item[ 'title' ];
			}

			if ( $post->post_content != $translation_item[ 'content' ] ) {
				$post->post_content = $translation_item[ 'content' ];
			}

			array_push( $posts, $post );
		}

		return $posts;
	}

	private function is_valid( $translation ) {

		return ! empty( $translation[ 'id' ] ) && ! empty( $translation[ 'title' ] ) && ! empty( $translation[ 'content' ] );
	}

	private function get_translation_item( $xliff ) {

		$ret = array();

		foreach ( $xliff->file as $item ) {
			$title   = $item->unit[ 0 ];
			$content = $item->unit[ 1 ];

			$ret[ 'title' ]   = $title->segment->target->pc->__toString();
			$ret[ 'content' ] = $content->segment->target->pc->__toString();

			foreach ( $title->segment->source->pc->attributes() as $key => $value ) {
				if ( $key == 'id' ) {
					$ret[ $key ] = $value->__toString();
					break;
				}
			}
		}

		return $ret;
	}

	private function get_xliff( $value ) {

		return simplexml_load_string( $value );
	}

	private function is_file_with_extension( $filename, $extension ) {

		$name = explode( ".", $filename );

		return strtolower( $name[ 1 ] ) == $extension ? TRUE : FALSE;
	}
}

class zip {

	var $total_files = 0;

	var $total_folders = 0;

	function Extract( $zn, $to, $index = Array( - 1 ) ) {

		$ok  = 0;
		$zip = @fopen( $zn, 'rb' );
		if ( ! $zip ) {
			return ( - 1 );
		}
		$cdir      = $this->ReadCentralDir( $zip, $zn );
		$pos_entry = $cdir[ 'offset' ];

		if ( ! is_array( $index ) ) {
			$index = array( $index );
		}
		for ( $i = 0; $index[ $i ]; $i ++ ) {
			if ( intval( $index[ $i ] ) != $index[ $i ] || $index[ $i ] > $cdir[ 'entries' ] ) {
				return ( - 1 );
			}
		}
		for ( $i = 0; $i < $cdir[ 'entries' ]; $i ++ ) {
			@fseek( $zip, $pos_entry );
			$header            = $this->ReadCentralFileHeaders( $zip );
			$header[ 'index' ] = $i;
			$pos_entry         = ftell( $zip );
			@rewind( $zip );
			fseek( $zip, $header[ 'offset' ] );
			if ( in_array( "-1", $index ) || in_array( $i, $index ) ) {
				$stat[ $header[ 'filename' ] ] = $this->ExtractFile( $header, $to, $zip );
			}
		}
		fclose( $zip );

		return $stat;
	}

	function ReadFileHeader( $zip ) {

		$binary_data = fread( $zip, 30 );
		$data        = unpack(
			'vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len',
			$binary_data
		);

		$header[ 'filename' ] = fread( $zip, $data[ 'filename_len' ] );
		if ( $data[ 'extra_len' ] != 0 ) {
			$header[ 'extra' ] = fread( $zip, $data[ 'extra_len' ] );
		} else {
			$header[ 'extra' ] = '';
		}

		$header[ 'compression' ]     = $data[ 'compression' ];
		$header[ 'size' ]            = $data[ 'size' ];
		$header[ 'compressed_size' ] = $data[ 'compressed_size' ];
		$header[ 'crc' ]             = $data[ 'crc' ];
		$header[ 'flag' ]            = $data[ 'flag' ];
		$header[ 'mdate' ]           = $data[ 'mdate' ];
		$header[ 'mtime' ]           = $data[ 'mtime' ];

		if ( $header[ 'mdate' ] && $header[ 'mtime' ] ) {
			$hour              = ( $header[ 'mtime' ] & 0xF800 ) >> 11;
			$minute            = ( $header[ 'mtime' ] & 0x07E0 ) >> 5;
			$seconde           = ( $header[ 'mtime' ] & 0x001F ) * 2;
			$year              = ( ( $header[ 'mdate' ] & 0xFE00 ) >> 9 ) + 1980;
			$month             = ( $header[ 'mdate' ] & 0x01E0 ) >> 5;
			$day               = $header[ 'mdate' ] & 0x001F;
			$header[ 'mtime' ] = mktime( $hour, $minute, $seconde, $month, $day, $year );
		} else {
			$header[ 'mtime' ] = time();
		}

		$header[ 'stored_filename' ] = $header[ 'filename' ];
		$header[ 'status' ]          = "ok";

		return $header;
	}

	function ReadCentralFileHeaders( $zip ) {

		$binary_data = fread( $zip, 46 );
		$header      = unpack(
			'vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset',
			$binary_data
		);

		if ( $header[ 'filename_len' ] != 0 ) {
			$header[ 'filename' ] = fread( $zip, $header[ 'filename_len' ] );
		} else {
			$header[ 'filename' ] = '';
		}

		if ( $header[ 'extra_len' ] != 0 ) {
			$header[ 'extra' ] = fread( $zip, $header[ 'extra_len' ] );
		} else {
			$header[ 'extra' ] = '';
		}

		if ( $header[ 'comment_len' ] != 0 ) {
			$header[ 'comment' ] = fread( $zip, $header[ 'comment_len' ] );
		} else {
			$header[ 'comment' ] = '';
		}

		if ( $header[ 'mdate' ] && $header[ 'mtime' ] ) {
			$hour              = ( $header[ 'mtime' ] & 0xF800 ) >> 11;
			$minute            = ( $header[ 'mtime' ] & 0x07E0 ) >> 5;
			$seconde           = ( $header[ 'mtime' ] & 0x001F ) * 2;
			$year              = ( ( $header[ 'mdate' ] & 0xFE00 ) >> 9 ) + 1980;
			$month             = ( $header[ 'mdate' ] & 0x01E0 ) >> 5;
			$day               = $header[ 'mdate' ] & 0x001F;
			$header[ 'mtime' ] = mktime( $hour, $minute, $seconde, $month, $day, $year );
		} else {
			$header[ 'mtime' ] = time();
		}
		$header[ 'stored_filename' ] = $header[ 'filename' ];
		$header[ 'status' ]          = 'ok';
		if ( substr( $header[ 'filename' ], - 1 ) == '/' ) {
			$header[ 'external' ] = 0x41FF0010;
		}

		return $header;
	}

	function ReadCentralDir( $zip, $zip_name ) {

		$size = filesize( $zip_name );

		if ( $size < 277 ) {
			$maximum_size = $size;
		} else {
			$maximum_size = 277;
		}

		@fseek( $zip, $size - $maximum_size );
		$pos   = ftell( $zip );
		$bytes = 0x00000000;

		while ( $pos < $size ) {
			$byte  = @fread( $zip, 1 );
			$bytes = ( $bytes << 8 ) | ord( $byte );
			if ( $bytes == 0x504b0506 or $bytes == 0x2e706870504b0506 ) {
				$pos ++;
				break;
			}
			$pos ++;
		}

		$fdata = fread( $zip, 18 );

		$data = @unpack( 'vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $fdata );

		if ( $data[ 'comment_size' ] != 0 ) {
			$centd[ 'comment' ] = fread( $zip, $data[ 'comment_size' ] );
		} else {
			$centd[ 'comment' ] = '';
		}
		$centd[ 'entries' ]      = $data[ 'entries' ];
		$centd[ 'disk_entries' ] = $data[ 'disk_entries' ];
		$centd[ 'offset' ]       = $data[ 'offset' ];
		$centd[ 'disk_start' ]   = $data[ 'disk_start' ];
		$centd[ 'size' ]         = $data[ 'size' ];
		$centd[ 'disk' ]         = $data[ 'disk' ];

		return $centd;
	}

	function ExtractFile( $header, $to, $zip ) {

		$header = $this->readfileheader( $zip );

		if ( substr( $to, - 1 ) != "/" ) {
			$to .= "/";
		}
		if ( $to == './' ) {
			$to = '';
		}
		$pth         = explode( "/", $to . $header[ 'filename' ] );
		$mydir       = '';
		$binary_data = FALSE;
		for ( $i = 0; $i < count( $pth ) - 1; $i ++ ) {
			if ( ! $pth[ $i ] ) {
				continue;
			}
			$mydir .= $pth[ $i ] . "/";
			if ( ( ! is_dir( $mydir )
					&& @mkdir(
						$mydir, 0777
					) )
				|| ( ( $mydir == $to . $header[ 'filename' ] || ( $mydir == $to && $this->total_folders == 0 ) )
					&& is_dir(
						$mydir
					) )
			) {
				@chmod( $mydir, 0777 );
				$this->total_folders ++;
				echo "<input name='dfile[]' type='checkbox' value='$mydir' checked> <a href='$mydir' target='_blank'><strong>Directory: $mydir</strong></a><br>";
			}
		}

		if ( strrchr( $header[ 'filename' ], '/' ) == '/' ) {
			return;
		}

		if ( ! ( $header[ 'external' ] == 0x41FF0010 ) && ! ( $header[ 'external' ] == 16 ) ) {
			if ( $header[ 'compression' ] == 0 ) {
				$fp = @fopen( $to . $header[ 'filename' ], 'wb' );
				if ( ! $fp ) {
					return ( - 1 );
				}
				$size = $header[ 'compressed_size' ];

				while ( $size != 0 ) {
					$read_size   = ( $size < 2048 ? $size : 2048 );
					$buffer      = fread( $zip, $read_size );
					$binary_data = pack( 'a' . $read_size, $buffer );
					@fwrite( $fp, $binary_data, $read_size );
					$size -= $read_size;
				}
				fclose( $fp );
				// touch($to.$header['filename'], $header['mtime']);
			} else {
				$fp = @fopen( $to . $header[ 'filename' ] . '.gz', 'wb' );
				if ( ! $fp ) {
					return ( - 1 );
				}
				$binary_data = pack(
					'va1a1Va1a1', 0x8b1f, Chr( $header[ 'compression' ] ),
					Chr( 0x00 ), time(), Chr( 0x00 ), Chr( 3 )
				);

				fwrite( $fp, $binary_data, 10 );
				$size = $header[ 'compressed_size' ];

				while ( $size != 0 ) {
					$read_size   = ( $size < 1024 ? $size : 1024 );
					$buffer      = fread( $zip, $read_size );
					$binary_data = pack( 'a' . $read_size, $buffer );
					@fwrite( $fp, $binary_data, $read_size );
					$size -= $read_size;
				}

				$binary_data = pack( 'VV', $header[ 'crc' ], $header[ 'size' ] );
				fwrite( $fp, $binary_data, 8 );
				fclose( $fp );

				$gzp = @gzopen( $to . $header[ 'filename' ] . '.gz', 'rb' ) or die( "Failed to create directory" );
				if ( ! $gzp ) {
					return ( - 2 );
				}
				$fp = @fopen( $to . $header[ 'filename' ], 'wb' );
				if ( ! $fp ) {
					return ( - 1 );
				}
				$size = $header[ 'size' ];

				while ( $size != 0 ) {
					$read_size   = ( $size < 2048 ? $size : 2048 );
					$buffer      = gzread( $gzp, $read_size );
					$binary_data = pack( 'a' . $read_size, $buffer );
					@fwrite( $fp, $binary_data, $read_size );
					$size -= $read_size;
				}
				fclose( $fp );
				gzclose( $gzp );

				//touch($to.$header['filename'], $header['mtime']);
				@unlink( $to . $header[ 'filename' ] . '.gz' );

			}
		}

		return $binary_data;
	}

	// end class
}