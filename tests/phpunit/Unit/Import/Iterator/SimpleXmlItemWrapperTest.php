<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper,
	Brain,
	SimpleXMLElement;

class SimpleXmlItemWrapperTest extends Helper\MonkeyTestCase {

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

		$testee = new Iterator\SimpleXmlItemWrapper(
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

	/**
	 * this test is also a proof of concept on how to handle namespaced items
	 */
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

		$namespaces = [ 'wp' => 'whatever' ];
		$testee = new Iterator\SimpleXmlItemWrapper(
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

	public function test_current_with_invalid_xml() {

		$xml_string =
			"<item><foo <bar & \">/item>";
		$iterator_mock = $this->getMock( 'Iterator' );
		$iterator_mock->method( 'current' )
			->willReturn(
				$xml_string
			);

		$wp_error_mock   = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->once() )
			->method( 'wp_error' )
			->with(
				'xml',
				$this->callback( 'is_string' )
			)
			->willReturn( $wp_error_mock );

		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with(
				$this->callback(
					function( $data ) use ( $xml_string ) {

						return
							FALSE !== strpos( $data[ 'data' ][ 'xml_string' ], $xml_string )
							&& is_array( $data[ 'data' ][ 'xml_errors' ] )
							&& 0 < count( $data[ 'data' ][ 'xml_errors' ] );

					}
				),
				'xml'
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_xml_parser_error' )
			->with( $wp_error_mock );

		// check the previous state is not affected by the object
		libxml_use_internal_errors( FALSE );
		$testee = new Iterator\SimpleXmlItemWrapper(
			$iterator_mock,
			[],
			'root',
			[],
			$wp_factory_mock
		);

		$testee->current();

		// check the
		$this->assertFalse( libxml_use_internal_errors() );

	}
}
