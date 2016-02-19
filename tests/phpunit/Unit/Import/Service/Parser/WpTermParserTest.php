<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Parser;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement,
	Brain;

class WpTermParserTest extends Helper\MonkeyTestCase {

	/**
	 * Tests parse_term with a XML document considered valid
	 *
	 * @dataProvider parse_term_test_data
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_term( SimpleXMLElement $document, Array $expected ) {


		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )->method( 'wp_error' );

		$testee = new Service\Parser\WpTermParser( $wp_factory_mock );

		// we don't expect an error here
		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_term_error' )
			->never();

		$result = $testee->parse_term( $document );
		$this->assertInstanceOf(
			'W2M\Import\Type\ImportTermInterface',
			$result
		);
		foreach ( $expected[ 'term_data' ] as $attribute => $expected ) {
			$this->assertSame(
				$expected,
				$result->{$attribute}(),
				"Test failed for method '{$attribute}'"
			);
		}
	}

	/**
	 * @see test_parse_term
	 *
	 * @return array
	 */
	public function parse_term_test_data() {

		$data = [];

		/**
		 * Valid XML, global namespace
		 */
		$term_data = [
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => 'It doesn\'t really matter what stands here.'
		];
		// Todo: Locale Relations

		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<wp:term_id>{$term_data[ 'origin_id' ]}</wp:term_id>
		<wp:category_nicename><![CDATA[{$term_data[ 'slug' ] }]]></wp:category_nicename >
		<wp:category_parent>{$term_data[ 'origin_parent_term_id' ]}</wp:category_parent >
		<wp:cat_name><![CDATA[{$term_data[ 'name' ]}]]></wp:cat_name>
		<wp:category_description><![CDATA[{$term_data[ 'description' ]}]]></wp:category_description>
		<wp:taxonomy>{$term_data[ 'taxonomy' ]}</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'valid_xml_global_ns' ] = [
			# 1. Parameter $document,
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'term_data' => $term_data
			]
		];

		/**
		 * Valid term, local namespace
		 */

		/**
		 * Valid XML, global namespace
		 */
		$term_data = [
			'origin_id'             => 41,
			'slug'                  => 'cat-pics',
			'origin_parent_term_id' => 0,
			'name'                  => 'Cat Pictures',
			'taxonomy'              => 'post_tag',
			'description'           => 'My best cat photos.'
		];
		// Todo: Locale Relations

		$xml = <<<XML
<root>
	<wp:category xmlns:wp="http://wordpress.org/export/1.2/">
		<wp:term_id>{$term_data[ 'origin_id' ]}</wp:term_id>
		<wp:category_nicename><![CDATA[{$term_data[ 'slug' ] }]]></wp:category_nicename >
		<wp:category_parent>{$term_data[ 'origin_parent_term_id' ]}</wp:category_parent >
		<wp:cat_name><![CDATA[{$term_data[ 'name' ]}]]></wp:cat_name>
		<wp:category_description><![CDATA[{$term_data[ 'description' ]}]]></wp:category_description>
		<wp:taxonomy>{$term_data[ 'taxonomy' ]}</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'valid_xml_local_ns' ] = [
			# 1. Parameter $document,
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'term_data' => $term_data
			]
		];

		return $data;
	}

	/**
	 * Test the behaviour when the expected namespace is missing
	 */
	public function test_parse_term_missing_namespace_error() {

		$xml = <<<XML
<root
	xmlns:notwp="urn:whatever"
>
	<notwp:category>
		<notwp:term_id>123</notwp:term_id>
		<notwp:category_nicename><![CDATA[slug]]></notwp:category_nicename >
		<notwp:category_parent>10</notwp:category_parent >
		<notwp:cat_name><![CDATA[Name]]></notwp:cat_name>
		<notwp:category_description><![CDATA[Description]]></notwp:category_description>
		<notwp:taxonomy>category</notwp:taxonomy>
	</notwp:category>
</root>
XML;
		$item            = new SimpleXMLElement( $xml );
		$wp_error_mock   = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				$this->callback( function( $context_data ) use ( $item ) {
					return
						   'wp' === $context_data[ 'data' ][ 'namespace' ]
						&& $item === $context_data[ 'data' ][ 'document' ];
				} ),
				'namespace'
			);

		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'namespace', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpTermParser( $wp_factory_mock );

		$result = $testee->parse_term( $item );
		$this->assertNull( $result );
	}

	/**
	 * Test the behaviour when an optional attribute is missing
	 *
	 * @dataProvider missing_attribute_test_data
	 * @param string $xml
	 * @param array $expected
	 */
	public function test_parse_term_missing_attribute_error( $xml, Array $expected ) {

		$item            = new SimpleXMLElement( $xml );
		$wp_error_mock   = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				$this->callback( function( $context_data ) use ( $expected, $item ) {
					return
						   $expected[ 'missing_attribute' ] === $context_data[ 'data' ][ 'attribute' ]
						&& $item === $context_data[ 'data' ][ 'document' ];
				} ),
				'attribute'
			);

		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'attribute', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpTermParser( $wp_factory_mock );

		$result = $testee->parse_term( $item );
		$this->assertInstanceOf(
			'W2M\Import\Type\ImportTermInterface',
			$result
		);
		foreach ( $expected[ 'term_data' ]  as $attribute => $expected ) {
			$this->assertSame(
				$expected,
				$result->{$attribute}(),
				"Test failed for method '{$attribute}'"
			);
		}
	}

	/**
	 * @see test_parse_term_missing_attribute_error
	 * @return array
	 */
	public function missing_attribute_test_data() {

		$data = [];

		$term_data = [
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		];
		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<wp:term_id>{$term_data[ 'origin_id' ]}</wp:term_id>
		<wp:category_nicename><![CDATA[{$term_data[ 'slug' ] }]]></wp:category_nicename >
		<wp:category_parent>{$term_data[ 'origin_parent_term_id' ]}</wp:category_parent >
		<wp:cat_name><![CDATA[{$term_data[ 'name' ]}]]></wp:cat_name>
		<!--
			The missing, optional item
			<wp:category_description><![CDATA[{$term_data[ 'description' ]}]]></wp:category_description>
		-->
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'missing_term_description' ] = [
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			[
				'term_data' => $term_data,
				'missing_attribute' => 'category_description'
			]
		];

		$term_data = [
			'origin_id'             => 0,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		];
		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<!--
			<wp:term_id>{$term_data[ 'origin_id' ]}</wp:term_id>
		-->
		<wp:category_nicename><![CDATA[{$term_data[ 'slug' ] }]]></wp:category_nicename >
		<wp:category_parent>{$term_data[ 'origin_parent_term_id' ]}</wp:category_parent >
		<wp:cat_name><![CDATA[{$term_data[ 'name' ]}]]></wp:cat_name>
		<wp:category_description><![CDATA[{$term_data[ 'description' ]}]]></wp:category_description>
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'missing_term_id' ] = [
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			[
				'term_data' => $term_data,
				'missing_attribute' => 'term_id'
			]
		];

		$term_data = [
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 0,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		];
		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<wp:term_id>{$term_data[ 'origin_id' ]}</wp:term_id>
		<wp:category_nicename><![CDATA[{$term_data[ 'slug' ] }]]></wp:category_nicename >
		<!--
			<wp:category_parent>{$term_data[ 'origin_parent_term_id' ]}</wp:category_parent >
		-->
		<wp:cat_name><![CDATA[{$term_data[ 'name' ]}]]></wp:cat_name>
		<wp:category_description><![CDATA[{$term_data[ 'description' ]}]]></wp:category_description>
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'missing_term_parent' ] = [
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			[
				'term_data' => $term_data,
				'missing_attribute' => 'category_parent'
			]
		];

		return $data;
	}

	/**
	 * Test the behaviour when a mandatory attribute is missing
	 *
	 * @dataProvider missing_mandatory_attribute_test_data
	 *
	 * @param string $xml
	 * @param array $expected
	 */
	public function test_parse_term_missing_mandatory_attribute_error( $xml, Array $expected ) {

		$item            = new SimpleXMLElement( $xml );
		$wp_error_mock   = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with(
				$this->callback( function( $context_data ) use ( $expected, $item ) {
					return
						   $context_data[ 'data' ][ 'attribute' ] === $expected[ 'missing_term' ]
						&& $item === $context_data[ 'data' ][ 'document' ];
				} ),
				'attribute'
			);

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );
		$testee = new Service\Parser\WpTermParser( $wp_factory_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$result = $testee->parse_term( $item );
		$this->assertNull(
			$result
		);
	}

	/**
	 * @see test_parse_term_missing_mandatory_attribute_error
	 */
	public function missing_mandatory_attribute_test_data() {

		$data = [];

		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<wp:term_id>1</wp:term_id>
		<wp:category_nicename>term</wp:category_nicename >
		<wp:category_parent>0</wp:category_parent >
		<!-- <wp:cat_name>Term</wp:cat_name> -->
		<wp:category_description>Description</wp:category_description>
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;

		$data[ 'missing_term_name' ] = [
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			[
				'missing_term' => 'cat_name'
			]
		];

		$xml = <<<XML
<root
	xmlns:wp="urn:what-ever"
>
	<wp:category>
		<wp:term_id>1</wp:term_id>
		<wp:category_nicename>term</wp:category_nicename >
		<wp:category_parent>0</wp:category_parent >
		<wp:cat_name>Term</wp:cat_name>
		<wp:category_description>Description</wp:category_description>
		<!--<wp:taxonomy>category</wp:taxonomy>-->
	</wp:category>
</root>
XML;

		$data[ 'missing_term_taxonomy' ] = [
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			[
				'missing_term' => 'taxonomy'
			]
		];

		return $data;
	}


	/**
	 * Test the behaviour when the complete item <wp:category/> is missing
	 */
	public function test_parse_term_missing_item_error() {

		$xml = <<<XML
<root
	xmlns:wp="urn:whatever"
>
	<wp:post>
		<wp:title>Some Title</wp:title>
	</wp:post>
</root>
XML;

		$item            = new SimpleXMLElement( $xml );
		$wp_error_mock   = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );

		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with(
				$this->callback( function( $context_data ) use ( $item ) {
					return
						   'category' === $context_data[ 'data' ][ 'item' ]
						&& $item === $context_data[ 'data' ][ 'document' ];
				} ),
				'item'
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpTermParser( $wp_factory_mock );
		$result = $testee->parse_term( $item );

		$this->assertNull(
			$result
		);
	}
}
