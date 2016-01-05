<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper,
	SimpleXMLElement;

class SimpleXmlItemParserTest extends \PHPUnit_Framework_TestCase {

	public function test_current_with_simple_xml() {

		$item_id = '1';
		$item_title = 'Hello World';
		$xml = <<<XML
<item>
	<id>{$item_id}</id>
	<title>{$item_title}</title>
</item>
XML;
		$node_iterator_mock = $this->getMock( 'Iterator' );
		$node_iterator_mock->expects( $this->any() )
			->method( 'current' )
			->willReturn( $xml );

		$testee = new Iterator\SimpleXmlItemParser(
			$node_iterator_mock
		);

		/* @type SimpleXMLElement $document */
		$document = $testee->current();
		$this->assertInstanceOf(
			'SimpleXMLElement',
			$document
		);

		$this->assertSame(
			$item_id,
			(string) $document->item->id
		);
		$this->assertSame(
			$item_title,
			(string) $document->item->title
		);
	}

	public function test_current_with_namespaced_xml() {

		$item_id = '1';
		$item_title = 'Hello World';
		$xml = <<<XML
<wp:item>
	<wp:id>{$item_id}</wp:id>
	<wp:title>{$item_title}</wp:title>
</wp:item>
XML;
		$node_iterator_mock = $this->getMock( 'Iterator' );
		$node_iterator_mock->expects( $this->any() )
			->method( 'current' )
			->willReturn( $xml );

		$namespaces = array( 'wp' => 'whatever' );
		$testee = new Iterator\SimpleXmlItemParser(
			$node_iterator_mock,
			$namespaces
		);

		/* @type SimpleXMLElement $document */
		$document = $testee->current();
		$this->assertInstanceOf(
			'SimpleXMLElement',
			$document
		);

		$doc_namespaces = $document->getDocNamespaces();
		$this->assertSame(
			$namespaces,
			$doc_namespaces
		);

		$wp_items = $document->children( $namespaces[ 'wp' ] );
		$this->assertSame(
			$item_id,
			(string) $wp_items->item->id
		);
		$this->assertSame(
			$item_title,
			(string) $wp_items->item->title
		);
	}
}
