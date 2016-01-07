<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement,
	Brain;

class WpTermParserTest extends Helper\UnitTestCase {

	/**
	 * Tests parse_term with a XML document considered valid
	 */
	public function test_parse_term() {

		$test_term = array(
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => 'It doesn\'t really matter what stands here.'
		);
		// Todo: Locale Relations
		$xml = <<<XML
<root
	xmlns:wp="what-ever"
>
	<wp:category>
		<wp:term_id>{$test_term[ 'origin_id' ]}</wp:term_id>
		<wp:category_nicename><![CDATA[{$test_term[ 'slug' ] }]]></wp:category_nicename >
		<wp:category_parent>{$test_term[ 'origin_parent_term_id' ]}</wp:category_parent >
		<wp:cat_name><![CDATA[{$test_term[ 'name' ]}]]></wp:cat_name>
		<wp:category_description><![CDATA[{$test_term[ 'description' ]}]]></wp:category_description>
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )->method( 'wp_error' );

		$item   = new SimpleXMLElement( $xml );
		$testee = new Service\WpTermParser( $wp_factory_mock );

		// we don't expect an error here
		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_term_error' )
			->never();

		$result = $testee->parse_term( $item );
		$this->assertInstanceOf(
			'W2M\Import\Type\ImportTermInterface',
			$result
		);
		foreach ( $test_term as $attribute => $expected ) {
			$this->assertSame(
				$expected,
				$result->{$attribute}(),
				"Test failed for method '{$attribute}'"
			);
		}
	}

	/**
	 * Test the behaviour when the expected namespace is missing
	 */
	public function test_parse_term_missing_namespace_error() {

		$xml = <<<XML
<root
	xmlns:notwp="whatever"
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
		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				'namespace',
				$this->callback( function( $context_data ) use ( $item ) {
					return
						   'wp' === $context_data[ 'data' ][ 'namespace' ]
						&& $item === $context_data[ 'data' ][ 'element' ];
				} )
			);

		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'namespace', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\WpTermParser( $wp_factory_mock );

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
		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				'attribute',
				$this->callback( function( $context_data ) use ( $expected, $item ) {
					return
						   $expected[ 'missing_attribute' ] === $context_data[ 'data' ][ 'attribute' ]
						&& $item === $context_data[ 'data' ][ 'element' ];
				} )
			);

		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'attribute', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\WpTermParser( $wp_factory_mock );

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

		$data = array();

		$term_data = array(
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		);
		$xml = <<<XML
<root
	xmlns:wp="what-ever"
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

		$data[ 'missing_term_description' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'term_data' => $term_data,
				'missing_attribute' => 'category_description'
			)
		);

		$term_data = array(
			'origin_id'             => 0,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		);
		$xml = <<<XML
<root
	xmlns:wp="what-ever"
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

		$data[ 'missing_term_id' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'term_data' => $term_data,
				'missing_attribute' => 'term_id'
			)
		);

		$term_data = array(
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 0,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
		);
		$xml = <<<XML
<root
	xmlns:wp="what-ever"
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

		$data[ 'missing_term_parent' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'term_data' => $term_data,
				'missing_attribute' => 'category_parent'
			)
		);

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
		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with(
				'attribute',
				$this->callback( function( $context_data ) use ( $expected, $item ) {
					return
						   $context_data[ 'data' ][ 'attribute' ] === $expected[ 'missing_term' ]
						&& $item === $context_data[ 'data' ][ 'element' ];
				} )
			);

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );
		$testee = new Service\WpTermParser( $wp_factory_mock );

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

		$data = array();

		$xml = <<<XML
<root
	xmlns:wp="what-ever"
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

		$data[ 'missing_term_name' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'missing_term' => 'cat_name'
			)
		);

		$xml = <<<XML
<root
	xmlns:wp="what-ever"
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

		$data[ 'missing_term_taxonomy' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'missing_term' => 'taxonomy'
			)
		);

		return $data;
	}


	/**
	 * Test the behaviour when the complete item <wp:category/> is missing
	 */
	public function test_parse_term_missing_item_error() {

		$xml = <<<XML
<root
	xmlns:wp="whatever"
>
	<wp:post>
		<wp:title>Some Title</wp:title>
	</wp:post>
</root>
XML;

		$item            = new SimpleXMLElement( $xml );
		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );

		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with(
				'item',
				$this->callback( function( $context_data ) use ( $item ) {
					return
						   'category' === $context_data[ 'data' ][ 'item' ]
						&& $item === $context_data[ 'data' ][ 'element' ];
				} )
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\WpTermParser( $wp_factory_mock );
		$result = $testee->parse_term( $item );

		$this->assertNull(
			$result
		);
	}
}
