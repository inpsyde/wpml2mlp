<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Filter;

use
	W2M\Import\Filter,
	W2M\Test\Helper;

class RecursiveIdMetaValueFilterTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider recursive_test_data
	 *
	 * @param mixed $value
	 * @param array $data
	 * @param bool $is_filterable
	 * @param array $expected
	 */
	public function test_filter( $value, Array $data, $is_filterable, $expected ) {

		$testee = new Filter\RecursiveIdMetaValueFilter(
			$data[ 'key_type_map' ],
			$this->build_id_map_mock( $data )
		);

		if ( $is_filterable ) {
			// might contain objects, so assertSame won't do it
			$this->assertEquals(
				$expected,
				$testee->filter( $value, 0 )
			);
		} else {
			$this->assertSame(
				$value,
				$testee->filter( $value, 0 )
			);
		}
	}

	/**
	 * @dataProvider recursive_test_data
	 *
	 * @param mixed $value
	 * @param array $data
	 * @param bool $is_filterable
	 */
	public function test_is_filterable( $value, Array $data, $is_filterable ) {

		$testee = new Filter\RecursiveIdMetaValueFilter(
			$data[ 'key_type_map' ],
			$this->build_id_map_mock( $data )
		);

		$this->assertSame(
			$is_filterable,
			$testee->is_filterable( $value, 0 )
		);
	}

	/**
	 * @dataProvider recursive_test_data
	 *
	 * @param mixed $value
	 * @param Array $data
	 * @param bool $unused
	 * @param Array|NULL $expected
	 */
	public function test_recursive_replacement( $value, Array $data, $unused, $expected ) {

		$testee = new Filter\RecursiveIdMetaValueFilter(
			$data[ 'key_type_map' ],
			$this->build_id_map_mock( $data )
		);

		if ( is_null( $expected ) ) {
			$this->assertSame(
				$value,
				$testee->recursive_replacement( $value )
			);
		} else {
			$this->assertEquals(
				$expected,
				$testee->recursive_replacement( $value )
			);
		}
	}

	/**
	 * @dataProvider recursive_test_data
	 *
	 * @param $value
	 * @param $data
	 * @param $expected
	 */
	public function test_recursive_lookup( $value, $data, $expected ) {

		$testee = new Filter\RecursiveIdMetaValueFilter(
			$data[ 'key_type_map' ],
			$this->build_id_map_mock( $data )
		);

		$this->assertSame(
			$expected,
			$testee->recursive_lookup( $value )
		);
	}

	/**
	 * @param array $data
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	private function build_id_map_mock( Array $data ) {

		$id_map_mock = $this->mock_builder->data_multi_type_id_mapper();
		$id_map_mock
			->method( 'local_id' )
			->with(
				$this->isType( 'string' ),
				$this->isType( 'int' )
			)
			->willReturnCallback(
				function( $type, $remote_id ) use ( $data ) {
					return isset( $data[ 'id_maps' ][ $type ][ $remote_id ] )
						? $data[ 'id_maps' ][ $type ][ $remote_id ]
						: 0;
				}
			);

		return $id_map_mock;
	}

	/**
	 * @see test_recursive_lookup
	 * @see test_recursive_replacement
	 * @see test_is_filterable
	 * @see test_filter
	 * @return array
	 */
	public function recursive_test_data() {

		$data = [];

		/**
		 * one level, single type
		 */
		$data[ 'test_1' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'something_else' => 'foo'
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post'
				],
				'id_maps' => [
					'post' => [
						12 => 21
					]
				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			TRUE,
			# 4. parameter $expected (test_recursive_replacement)
			[
				'post_reference' => 21,
				'something_else' => 'foo'
			]
		];

		/**
		 * one level, single type, not filterable
		 */
		$data[ 'test_2' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'something_else' => 'foo'
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post'
				],
				'id_maps' => [
					'post' => [
						13 => 21
					]
				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			FALSE,
			# 4. parameter $expected (test_recursive_replacement)
			[
				'post_reference' => 0, // as it was not mapped
				'something_else' => 'foo'
			]
		];

		/**
		 * one level, multiple types
		 */
		$data[ 'test_3' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'some_more_key'  => 12,
				'term_reference' => 42,
				'something_else' => FALSE
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post',
					'term_reference' => 'term'
				],
				'id_maps' => [
					'post' => [
						12 => 21
					],
					'term' => [
						42 => 24
					],

				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			TRUE,
			# 4. parameter $expected (test_recursive_replacement)
			[
				'post_reference' => 21,
				'some_more_key'  => 12,
				'term_reference' => 24,
				'something_else' => FALSE
			],
		];

		/**
		 * one level, multiple types, one not filterable
		 */
		$data[ 'test_4' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'some_more_key'  => 12,
				'term_reference' => 42,
				'something_else' => FALSE,
				'user_reference' => 2
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post',
					'term_reference' => 'term',
					'user_reference' => 'user'
				],
				'id_maps' => [
					'post' => [
						12 => 21
					],
					'term' => [
						42 => 24
					],
					'user' => [
						// no matching reference here
						1 => 3
					]

				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			FALSE,
			# 4. parameter $expected (test_recursive_replacement),
			[
				'post_reference' => 21, // mapped
				'some_more_key'  => 12,
				'term_reference' => 24, // mapped
				'something_else' => FALSE,
				'user_reference' => 0   // not mapped
			],
		];

		/**
		 * multiple levels, multiple types
		 */
		$data[ 'test_5' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'some_more_key'  => 12,
				'terms'          => [
					'term_reference' => 42,
				],
				'something_else' => (object) [
					'foo'  => 'bar',
					'bazz' => [
						'user_reference' => 2
					]
				],
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post',
					'term_reference' => 'term',
					'user_reference' => 'user'
				],
				'id_maps' => [
					'post' => [
						12 => 21
					],
					'term' => [
						42 => 24
					],
					'user' => [
						2 => 3
					]

				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			TRUE,
			# 4. parameter $expected (test_recursive_replacement)
			[
				'post_reference' => 21,
				'some_more_key'  => 12,
				'terms'          => [
					'term_reference' => 24,
				],
				'something_else' => (object) [
					'foo'  => 'bar',
					'bazz' => [
						'user_reference' => 3
					]
				],
			]
		];

		/**
		 * multiple levels, multiple types, not filterable
		 */
		$data[ 'test_6' ] = [
			# 1. parameter $value
			[
				'post_reference' => 12,
				'some_more_key'  => 12,
				'terms'          => [
					'term_reference' => 42,
				],
				'something_else' => (object) [
					'foo'  => 'bar',
					'bazz' => [
						'user_reference' => 2,
						'one_more_level' => [
							'post_reference' => 14 //This is not represented in the ID map
						]
					]
				],
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post',
					'term_reference' => 'term',
					'user_reference' => 'user'
				],
				'id_maps' => [
					'post' => [
						12 => 21
					],
					'term' => [
						42 => 24
					],
					'user' => [
						2 => 3
					]

				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			FALSE,
			# 4. parameter $expected (test_recursive_replacement),
			[
				'post_reference' => 21, //mapped
				'some_more_key'  => 12,
				'terms'          => [
					'term_reference' => 24, // napped
				],
				'something_else' => (object) [
					'foo'  => 'bar',
					'bazz' => [
						'user_reference' => 3, // mapped
						'one_more_level' => [
							'post_reference' => 0 // not mapped
						]
					]
				],
			]
		];

		/**
		 * objects all the way
		 */
		$data[ 'test_7' ] = [
			# 1. parameter $value
			(object) [
				'post_reference' => 12,
				'sub_list' => (object) [
					'yes_deeper' => (object) [
						'post_reference' => 42
					]
				]
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post'
				],
				'id_maps' => [
					'post' => [
						12 => 21,
						42 => 72
					]
				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			TRUE,
			# 4. parameter $expected (test_recursive_replacement),
			(object) [
				'post_reference' => 21,
				'sub_list' => (object) [
					'yes_deeper' => (object) [
						'post_reference' => 72
					]
				]
			],
		];

		/**
		 * objects all the way, not filterable
		 */
		$data[ 'test_8' ] = [
			# 1. parameter $value
			(object) [
				'post_reference' => 12,
				'sub_list' => (object) [
					'yes_deeper' => (object) [
						'post_reference' => 42
					]
				]
			],
			# 2. parameter $data
			[
				'key_type_map' => [
					'post_reference' => 'post'
				],
				'id_maps' => [
					'post' => [
						42 => 72
					]
				]
			],
			# 3. parameter $expected (test_recursive_lookup)
			TRUE,
			# 4. parameter $expected (test_recursive_replacement),
			(object) [
				'post_reference' => 0, // not filterable
				'sub_list' => (object) [
					'yes_deeper' => (object) [
						'post_reference' => 72
					]
				]
			],
		];

		return $data;
	}
}
