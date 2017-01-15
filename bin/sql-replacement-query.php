#!/usr/bin/env php
<?php # -*- coding: utf-8 -*-

/**
 * Create printer to STDOUT and STDERR
 */
$resource_line_printer = function( $resource ) {
	return function( $message ) use ( $resource ) {
		fwrite( $resource, "{$message}\n" );
	};
};
$error_line_printer = $resource_line_printer( STDERR );
$std_line_printer   = $resource_line_printer( STDOUT );
$error_handler      = function( $msg, $exit_code = 1 ) use ( $error_line_printer ) {
	$error_line_printer( $msg );
	exit( (int) $exit_code );
};

/**
 * Argument parser
 */
$get_argument_builder = function( $argv, $error_handler ) {
	return function( $index, $name ) use ( $argv, $error_handler ) {
		if ( isset( $argv[ $index ] ) )
			return $argv[ $index ];

		$error_handler( "Error: Missing parameter {$name}" );
	};
};
$get_argument = $get_argument_builder( $argv, $error_handler );

/**
 * Print the help text
 */
$print_usage = function() use ( $std_line_printer, $argv ) {

	$file  = basename( $argv[ 0 ] );
	$usage = <<<STR
Creates SQL update query

	Reads a JSON report file from STDIN and creates SQL update commands.

Synopsis

	./{$file} <TABLE> <COLUMN> <TYPE> [-h|--help]

Example

	./{$file} wp_woocommerce_orders product_id post < w2m_import_report.json > update.sql

Options

	-h,--help  Print this help message
STR;

	$std_line_printer( $usage );
};

/**
 * Quotes a mysql query identifier
 */
$quote_identifier = function( $identifier ) {
	$identifier = str_replace( '`', '``', $identifier );

	return "`{$identifier}`";
};

if ( in_array( '-h', $argv ) || in_array( '--help', $argv ) ) {
	$print_usage();
	exit( 0 );
}

$type     = $get_argument( 3, 'type' );
$map_type = "{$type}s"; // types are referenced as plural
$column   = $get_argument( 2, 'column' );
$table    = $get_argument( 1, 'table' );

$report = json_decode( file_get_contents( 'php://stdin', 'r' ) );
if ( ! is_object( $report ) && ! $report->maps )
	$error_handler( "ERROR: The report seems to be broken." );

if ( ! isset( $report->maps->{ $map_type } ) )
	$error_handler( "ERROR: Unknown type '{$type}' in report" );

$id_map   = get_object_vars( $report->maps->{ $map_type  } );
$sql_when = '';
foreach ( $id_map as $origin_id => $id ) {
	$sql_when .= sprintf(
		"WHEN %s = %d THEN %d\n",
		$quote_identifier( $column ),
		$origin_id,
		$id
	);
}

$sql = <<<SQL
-- Replacement for {$quote_identifier($table)}.{$quote_identifier($column)}
UPDATE {$quote_identifier($table)}
SET {$quote_identifier($column)} = (
		CASE
			{$sql_when}
		ELSE {$quote_identifier($column)}
		END
);
SQL;

$std_line_printer( $sql );
exit( 0 );