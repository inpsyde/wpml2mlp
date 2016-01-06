<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement,
	Brain;

class WpTermParserTest extends Helper\MonkeyTestCase {

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

		$wp_factory_mock = $this->getMockBuilder( 'W2M\Import\Common\WpFactory' )
			->getMock();
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

	public function test_parse_term_missing_namespace_error() {

		$xml = <<<XML
<root>
	<category>
		<term_id>123</term_id>
		<category_nicename><![CDATA[slug]]></category_nicename >
		<category_parent>10</category_parent >
		<cat_name><![CDATA[Name]]></cat_name>
		<category_description><![CDATA[Description]]></category_description>
		<taxonomy>category</taxonomy>
	</category>
</root>
XML;
		$wp_error_mock = $this->getMockBuilder( 'WP_Error' )
			->setMethods( array( 'add_data' ) )
			->getMock();
		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				'namespace',
				$this->callback( 'is_array' )
			);

		$wp_factory_mock = $this->getMockBuilder( 'W2M\Import\Common\WpFactory' )
			->getMock();
		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'namespace', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$item = new SimpleXMLElement( $xml );
		$testee = new Service\WpTermParser( $wp_factory_mock );

		$result = $testee->parse_term( $item );
		$this->assertNull( $result );
	}

	public function test_parse_term_missing_attribute_error() {

		$test_term = array(
			'origin_id'             => 112,
			'slug'                  => 'top-news',
			'origin_parent_term_id' => 110,
			'name'                  => 'Top News',
			'taxonomy'              => 'category',
			'description'           => ''
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
		<!--
			The missing, optional item
			<wp:category_description><![CDATA[{$test_term[ 'description' ]}]]></wp:category_description>
		-->
		<wp:taxonomy>category</wp:taxonomy>
	</wp:category>
</root>
XML;
		$wp_error_mock = $this->getMockBuilder( 'WP_Error' )
			->setMethods( array( 'add_data' ) )
			->getMock();
		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with(
				'attribute',
				$this->callback( 'is_array' )
			);

		$wp_factory_mock = $this->getMockBuilder( 'W2M\Import\Common\WpFactory' )
			->getMock();
		$wp_factory_mock->expects( $this->any() )
			->method( 'wp_error' )
			->with( 'attribute', $this->callback( 'is_string' ) )
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()->expectFired( 'w2m_import_parse_term_error' )
			->once()
			->with( $wp_error_mock );

		$item = new SimpleXMLElement( $xml );
		$testee = new Service\WpTermParser( $wp_factory_mock );

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

	public function test_parse_term_missing_mandatory_attribute_error() {

		$this->markTestIncomplete( 'Under construction' );
	}

	public function test_parse_term_missing_item_error() {

		$this->markTestIncomplete( 'Under construction' );
	}
}
