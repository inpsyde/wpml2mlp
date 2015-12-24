<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\ObjectCreation;

use
	W2M\Import\Service;

class WpImportObjectBuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider build_term_test_data
	 * @param string $xml
	 * @param array $expected
	 */
	public function test_build_import_term( $xml, Array $expected ) {



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
		<wp:category_nicename><![CDATA[anti-aging-nachrichten]]></wp:category_nicename >
		<wp:category_parent><![CDATA[]]> </wp:category_parent >
		<wp:cat_name><![CDATA[Anti-Aging]]></wp:cat_name>
		<wp:category_description><![CDATA[]]></wp:category_description>
	</item>
</root>
XML;


		$data[ 'first_test' ] = array(
			# 1. Parameter $xml
			$xml,
			# 2. Parameter $expected
			array(

			)
		);

		return $data;
	}
}
