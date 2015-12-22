<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper;

class SimpleXmlItemParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider current_test_data
	 * @param $xml
	 * @param array $expected
	 */
	public function test_current( $xml, Array $expected ) {

		$node_iterator_mock = $this->getMock( 'Iterator' );
		$node_iterator_mock->expects( $this->any() )
			->method( 'current' )
			->willReturn( $xml );

		$testee = new Iterator\SimpleXmlItemParser(
			$node_iterator_mock
		);

		$document = $testee->current();
		$this->assertInstanceOf(
			'SimpleXMLElement',
			$document
		);
	}

	/**
	 * @see test_current
	 * @return array
	 */
	public function current_test_data() {

		$data = array();

		$xml = <<<XML
<item>
	<id>1</id>
	<title>Hello World</title>
</item>
XML;

		$data[ 'single_item' ] = array(
			# 1.Parameter $xml
			$xml,
			# 2.Parameter $expected
			array(
				'parameter' => array(
					'id' => '1',
					'title' => 'Hello World'
				)
			)
		);

		return $data;

	}
}
