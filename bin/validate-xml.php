#!/usr/bin/env php
<?php # -*- coding: utf-8 -*-

/**
 * Quick »5-minutes-script« to validate all *.xml files in a given directory
 *
 * Usage: php validate-xml.php path/to/directory
 */

function print_usage() {

	$file = basename( __FILE__ );
	$str = <<<STR
Validate-xml
	checks each xml file in a directory for validity.

Usage
	php {$file} <DIRECTORY>

STR;

	echo $str;
}

function start_timer() {

	$GLOBALS[ 'start_time' ] = microtime( TRUE );
}
function stop_timer() {

	return microtime( TRUE ) - $GLOBALS[ 'start_time' ];
}

function format_xml_error( libXMLError $error ) {

	switch ( $error->level ) {
		case LIBXML_ERR_WARNING :
			$level = 'WARNING';
			break;

		case LIBXML_ERR_ERROR :
			$level = 'ERROR';
			break;

		case LIBXML_ERR_FATAL :
			$level = 'FATAL';
			break;

		default:
			$level = 'NOTICE';
			break;
	}

	$msg = "{$level}({$error->code}) at line {$error->line}:{$error->column}: {$error->message}";

	return $msg;
}

/**
 * @link https://secure.php.net/manual/de/function.memory-get-usage.php#96280
 *
 * @param $size
 *
 * @return string
 */
function convert_memory_size( $size )
{
	$unit = [ 'b', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' ];
	return round( $size / pow( 1024, ( $i = floor ( log( $size, 1024 ) ) ) ), 2 ).' '.$unit[ $i ];
}

if ( empty( $argv[ 1 ] ) ) {
	print_usage();
	exit;
}
start_timer();
$dir = realpath( $argv[ 1 ] );
$iterator = new DirectoryIterator( $dir );
libxml_use_internal_errors( TRUE );

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() )
		continue;

	if ( '.xml' !== substr( $file->getBasename(), -4 ) )
		continue;

	$document = simplexml_load_file( $file->getPathname() );
	if ( is_a( $document, 'SimpleXMLElement' ) ) {
		echo "File {$file->getFilename()} is valid XML.\n";
	} else {
		echo "File {$file->getFilename()} is not valid:\n";
		foreach ( libxml_get_errors() as $error ) {
			echo "\t" . format_xml_error( $error );
		}
	}
}

$runtime = number_format( stop_timer(), 3 );
$memory_usage = convert_memory_size( memory_get_peak_usage( TRUE ) );
echo "Finished check in {$runtime} s. Memory Usage {$memory_usage}\n";
return 0;
