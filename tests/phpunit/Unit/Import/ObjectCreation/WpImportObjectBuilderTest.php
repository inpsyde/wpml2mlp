<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\ObjectCreation;

use
	W2M\Import\ObjectCreation,
	SimpleXMLElement;

class WpImportObjectBuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider build_term_test_data
	 * @param string $xml
	 * @param array $expected
	 */
	public function test_build_import_term( $xml, Array $expected ) {

		$testee  = new ObjectCreation\WpImportObjectBuilder;
		$element = new SimpleXMLElement( $xml );

		$import_term = $testee->build_import_term( $element );

		$this->markTestIncomplete( 'Under construction…' );
		$this->assertInstanceOf(
			$expected[ 'expected_type' ],
			$import_term
		);

	}

	/**
	 * @see test_build_import_term
	 * @return array
	 */
	public function build_term_test_data() {

		$data = array();

		$xml = <<<XML
<root
	xmlns:wp="wp"
	>
	<item>
		<wp:term_id>96</wp:term_id>
		<wp:category_nicename><![CDATA[my-topic-news]]></wp:category_nicename >
		<wp:category_parent><![CDATA[]]> </wp:category_parent >
		<wp:cat_name><![CDATA[My Topic]]></wp:cat_name>
		<wp:category_description><![CDATA[Some of my favorite topics]]></wp:category_description>
	</item>
</root>
XML;


		$data[ 'first_test' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(
				'expected_type' => 'W2M\Import\Type\ImportTermInterface',
				'expected_data' => array(
					/* @see ImportTermInterface */
					'taxonomy'    => '', // not implemented yet
					'name'        => 'My Topic',
					'slug'        => 'my-topic-news',
					'description' => 'Some of my favorite topics',
					'origin_parent_term_id' => 0,
					'locale_relations' => array()
				)
			)
		);

		return $data;
	}
}
