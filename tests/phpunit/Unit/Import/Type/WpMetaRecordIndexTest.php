<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Type;

use Brain\Monkey\Functions;
use
	W2M\Import\Type,
	W2M\Test\Helper;

class WpMetaRecordIndexTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider setter_getter_test_data
	 *
	 * @param array $data
	 */
	public function test_setter_and_getter( Array $data ) {

		$testee = new Type\WpMetaRecordIndex(
			$data[ 'key' ],
			$data[ 'object_id' ],
			$data[ 'index' ],
			$data[ 'type' ]
		);

		foreach ( $data as $method => $value ) {
			$this->assertSame(
				$value,
				$testee->{$method}(),
				"Test failed for method {$method}()"
			);
		}
	}

	public function test_type_casting() {

		$data = [
			'key'       => 12,
			'object_id' => '42',
			'index'     => '3',
			'type'      => 53
		];
		$testee = new Type\WpMetaRecordIndex(
			$data[ 'key' ],
			$data[ 'object_id' ],
			$data[ 'index' ],
			$data[ 'type' ]
		);

		$this->assertSame(
			(string) $data[ 'key' ],
			$testee->key()
		);
		$this->assertSame(
			(int) $data[ 'object_id' ],
			$testee->object_id()
		);
		$this->assertSame(
			(int) $data[ 'index' ],
			$testee->index()
		);
		$this->assertSame(
			(string) $data[ 'type' ],
			$testee->type()
		);

	}

	/**
	 * @see test_setter_and_getter
	 * @return array
	 */
	public function setter_getter_test_data() {

		$data = [];

		$data[ 'test_1' ] = [
			# 1. parameter $data
			[
				'key'       => '_thumbnail_id',
				'object_id' => 42,
				'index'     => 1,
				'type'      => 'post'
			]
		];

		$data[ 'test_2' ] = [
			# 1. parameter $data
			[
				'key'       => 'some_comment_meta',
				'object_id' => 12,
				'index'     => 0,
				'type'      => 'comment'
			]
		];

		$data[ 'test_3' ] = [
			# 1. parameter $data
			[
				'key'       => 'some_term_meta',
				'object_id' => 1,
				'index'     => 0,
				'type'      => 'term'
			]
		];

		$data[ 'test_4' ] = [
			# 1. parameter $data
			[
				'key'       => 'some_user_meta',
				'object_id' => 0,
				'index'     => 0,
				'type'      => 'user'
			]
		];

		return $data;
	}
}
