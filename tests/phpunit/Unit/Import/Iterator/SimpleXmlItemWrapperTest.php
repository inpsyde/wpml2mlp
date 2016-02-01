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
		$namespaces = [ 'wp' => 'urn:whatever' ];
		$meta = [
			[
				'key' => '_edit_lock',
				'value' => '1453111692:688'
			],
			[
				'key' => '_edit_last',
				'value' => '9'
			]
		];
		$xml = <<<XML
<wp:item xmlns:wp="{$namespaces[ 'wp' ]}">
	<wp:id>{$item_id}</wp:id>
	<wp:title>{$item_title}</wp:title>
	<wp:postmeta>
		<wp:meta_key><![CDATA[{$meta[ 0 ][ 'key' ]}]]></wp:meta_key>
		<wp:meta_value><![CDATA[{$meta[ 0 ][ 'value' ]}]]></wp:meta_value>
	</wp:postmeta>
	<wp:postmeta>
		<wp:meta_key><![CDATA[{$meta[ 1 ][ 'key' ]}]]></wp:meta_key>
		<wp:meta_value><![CDATA[{$meta[ 1 ][ 'value' ]}]]></wp:meta_value>
	</wp:postmeta>
</wp:item>
XML;
		$node_iterator_mock = $this->getMock( 'Iterator' );
		$node_iterator_mock->expects( $this->any() )
			->method( 'current' )
			->willReturn( $xml );

		$testee = new Iterator\SimpleXmlItemWrapper( $node_iterator_mock );

		/* @type SimpleXMLElement $document */
		$document = $testee->current();
		$this->assertInstanceOf(
			'SimpleXMLElement',
			$document
		);

		$doc_namespaces = $document->getDocNamespaces( TRUE );
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

		foreach ( $meta as $index => $values ) {
			$this->assertSame(
				$values[ 'key' ],
				(string) $wp_items->item->postmeta[ $index ]->meta_key
			);
			$this->assertSame(
				$values[ 'value' ],
				(string) $wp_items->item->postmeta[ $index ]->meta_value
			);
		}
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
			'root',
			[],
			$wp_factory_mock
		);

		$testee->current();

		// check the
		$this->assertFalse( libxml_use_internal_errors() );

	}
}
