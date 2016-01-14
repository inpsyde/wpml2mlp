<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper,
	SimpleXMLElement;

class PostIteratorTest extends Helper\MonkeyTestCase {

	public function test_current() {

		/**
		 * there are problems when mocking SimpleXMLELement
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/141
		 */
		$sxml             = new SimpleXMLElement( '<root/>' );
		$iterator_mock    = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$parser_mock      = $this->mock_builder->service_post_parser_interface();
		$import_post_mock = $this->mock_builder->type_wp_import_post();

		$iterator_mock->expects( $this->once() )
			->method( 'current' )
			->willReturn( $sxml );

		$parser_mock->expects( $this->once() )
			->method( 'parse_post' )
			->with( $sxml )
			->willReturn( $import_post_mock );

		$testee = new Iterator\PostIterator( $iterator_mock, $parser_mock );
		$this->assertSame(
			$import_post_mock,
			$testee->current()
		);
	}
}
