<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import,
	W2M\Test\Helper,
	PHPUnit_Framework_MockObject_MockObject,
	Iterator;

class IteratorDecoratorsTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider testee_provider
	 *
	 * @param Iterator $testee
	 * @param PHPUnit_Framework_MockObject_MockObject $iterator_mock
	 */
	public function test_next( Iterator $testee, PHPUnit_Framework_MockObject_MockObject $iterator_mock ) {

		$iterator_mock->expects( $this->once() )
			->method( 'next' );

		$this->assertNull(
			$testee->next()
		);
	}

	/**
	 * @dataProvider testee_provider
	 *
	 * @param Iterator $testee
	 * @param PHPUnit_Framework_MockObject_MockObject $iterator_mock
	 */
	public function test_key( Iterator $testee, PHPUnit_Framework_MockObject_MockObject $iterator_mock ) {

		$key = uniqid();
		$iterator_mock->expects( $this->once() )
			->method( 'key' )
			->willReturn( $key );

		$this->assertSame(
			$key,
			$testee->key()
		);
	}

	/**
	 * @dataProvider testee_provider
	 *
	 * @param Iterator $testee
	 * @param PHPUnit_Framework_MockObject_MockObject $iterator_mock
	 */
	public function test_rewind( Iterator $testee, PHPUnit_Framework_MockObject_MockObject $iterator_mock ) {

		$iterator_mock->expects( $this->once() )
			->method( 'rewind' );

		$this->assertNull(
			$testee->rewind()
		);
	}

	/**
	 * @dataProvider testee_provider
	 *
	 * @param Iterator $testee
	 * @param PHPUnit_Framework_MockObject_MockObject $iterator_mock
	 */
	public function test_valid( Iterator $testee, PHPUnit_Framework_MockObject_MockObject $iterator_mock ) {

		$valid = (bool) rand( 0, 1 );
		$iterator_mock->expects( $this->once() )
			->method( 'valid' )
			->willReturn( $valid );

		$this->assertSame(
			$valid,
			$testee->valid()
		);
	}

	/**
	 * @see test_next
	 * @see test_key
	 * @return array
	 */
	public function testee_provider() {

		// parent::setUp is called after the first dataProvider
		if ( !$this->mock_builder ) {
			$this->mock_builder = new Helper\MockBuilder( $this );
		}

		$data = [ ];

		$post_iterator_mock      = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$post_parser_mock        = $this->mock_builder->service_post_parser_interface();
		$data[ 'post_iterator' ] = [
			# 1. parameter $testee
			new Import\Iterator\PostIterator( $post_iterator_mock, $post_parser_mock ),
			# 2. parameter $iterator_mock
			$post_iterator_mock
		];

		$user_iterator_mock      = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$user_parser_mock        = $this->mock_builder->service_user_parser_interface();
		$data[ 'user_iterator' ] = [
			# 1. parameter $testee
			new Import\Iterator\UserIterator( $user_iterator_mock, $user_parser_mock ),
			# 2. parameter $iterator_mock
			$user_iterator_mock
		];

		$term_iterator_mock      = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$term_parser_mock        = $this->mock_builder->service_term_parser_interface();
		$data[ 'term_iterator' ] = [
			# 1. parameter $testee
			new Import\Iterator\TermIterator( $term_iterator_mock, $term_parser_mock ),
			# 2. parameter $iterator_mock
			$term_iterator_mock
		];

		$comment_iterator_mock      = $this->mock_builder->iterator_simple_xml_item_wrapper();
		$user_parser_mock           = $this->mock_builder->service_comment_parser_interface();
		$data[ 'comment_iterator' ] = [
			# 1. parameter $testee
			new Import\Iterator\CommentIterator( $comment_iterator_mock, $user_parser_mock ),
			# 2. parameter $iterator_mock
			$comment_iterator_mock
		];

		return $data;
	}
}
