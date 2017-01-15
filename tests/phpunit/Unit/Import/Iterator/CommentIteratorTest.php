<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper,
	SimpleXMLElement;

class CommentIteratorTest extends Helper\MonkeyTestCase {

	public function test_current() {

		/**
		 * there are problems when mocking SimpleXMLELement
		 *
		 * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/141
		 */
		$sxml                = new SimpleXMLElement( '<root/>' );
		$iterator_mock       = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$parser_mock         = $this->mock_builder->service_comment_parser_interface();
		$import_comment_mock = $this->mock_builder->type_wp_import_comment();

		$iterator_mock->expects( $this->once() )
			->method( 'current' )
			->willReturn( $sxml );

		$parser_mock->expects( $this->once() )
			->method( 'parse_comment' )
			->with( $sxml )
			->willReturn( $import_comment_mock );

		$testee = new Iterator\CommentIterator(
			$iterator_mock,
			$parser_mock
		);
		$this->assertSame(
			$import_comment_mock,
			$testee->current()
		);
	}
}
