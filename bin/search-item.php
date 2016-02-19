#!/usr/bin/env php
<?php # -*- coding: utf-8 -*-

/**
 * Checks if an element with the given ID exists in the given WXR file.
 *
 * php search_item.php <FILE> <ID> [<TYPE>] [-h|--help]
 *
 */

function print_usage() {

	$usage = <<<STR
Search item

	Checks if an element with the given ID exists in the given WXR file.

Synopsis

	php search_item.php <FILE> <ID> [<TYPE>] [-h|--help] [--xml]

Options

	-h,--help  Print this help message
	--xml      Print the found item as xml

STR;

	print $usage;
}

/**
 * @param array $result
 * @param string $type
 * @param int $id
 */
function print_result( Array $result, $type, $id ) {

	$num = count( $result );
	$line_numbers = [];
	foreach ( $result as $element )
		$line_numbers[] = get_line_number( $element );

	$line_str = implode( ', ', $line_numbers );
	$str = <<<STR
Found {$num} {$type} with ID {$id} at line {$line_str}

STR;

	print $str;
}

/**
 * Prints xml of the parent element
 *
 * @param SimpleXMLElement $element
 */
function print_item_xml( SimpleXMLElement $element ) {

	$parent = $element->xpath( '..' )[ 0 ];

	print $parent->asXML() . "\n";
}

/**
 * @param SimpleXMLElement $element
 *
 * @return int
 */
function get_line_number( SimpleXMLElement $element ) {

	$dom_node = dom_import_simplexml( $element );

	return $dom_node->getLineNo();
}

if ( in_array( '-h', $argv ) || in_array( '--help', $argv ) ) {
	print_usage();
	return 0;
}

$file = isset( $argv[ 1 ] )
	? realpath( $argv[ 1 ] )
	: '';

if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
	echo "ERROR: File does not exist\n";

	return 1;
}

$id = isset( $argv[ 2 ] )
	? (int) $argv[ 2 ]
	: 0;
if ( ! $id ) {
	echo "ERROR: Invalid id\n";

	return 1;
}

$type = isset( $argv[ 3 ] )
	? $argv[ 3 ]
	: 'post';
if ( ! in_array( $type, [ 'post', 'term', 'user', 'comment' ] ) )
	$type = 'post';

$queries = [
	'post'    => '/rss/channel/item/wp:post_id[ text() = %d ]',
	'comment' => '/rss/channel/item/wp:comment/wp:comment_ID[ text() = %d ]',
	'user'    => '/rss/channel/wp:author/wp:author_id[ text() = %d ]',
	'term'    => '/rss/channel/wp:category/wp:term_id[ text() = %d ]'
];

$doc = new SimpleXMLElement( $file, 0, TRUE );
$result = $doc->xpath( sprintf ( $queries[ $type ], $id ) );

if ( in_array( '--xml', $argv ) && count( $result ) ) {
	print_item_xml( current( $result ) );
} else {
	print_result( $result, $type, $id );
}
return 0;