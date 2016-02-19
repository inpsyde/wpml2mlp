<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Data;

use
	W2M\Test\Helper,
	W2M\Import\Data;

class ImportListeningTypeIdMapperTest extends Helper\MonkeyTestCase {

	public function test_record_post() {

		$origin_id = 124;
		$local_id  = 523;

		$import_post_mock = $this->mock_builder->type_wp_import_post();
		$import_post_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_post_mock->method( 'id' )
			->willReturn( $local_id );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'post', $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'post', $origin_id )
		);

		$testee->record_post( $import_post_mock );

		$this->assertSame(
			$origin_id,
			$testee->origin_id( 'post', $local_id )
		);
		$this->assertSame(
			$local_id,
			$testee->local_id( 'post', $origin_id )
		);
	}

	public function test_record_term() {

		$origin_ids = [ 465, 632 ];
		$local_ids  = [ 632, 839 ];

		$import_term_mocks = [];
		$import_term_mocks[] = $this->mock_builder->type_wp_import_term();
		$import_term_mocks[ 0 ]->method( 'origin_id' )
			->willReturn( $origin_ids[ 0 ] );
		$import_term_mocks[ 0 ]->method( 'id' )
			->willReturn( $local_ids[ 0 ] );

		$import_term_mocks[] = $this->mock_builder->type_wp_import_term();
		$import_term_mocks[ 1 ] ->method( 'origin_id' )
			->willReturn( $origin_ids[ 1 ] );
		$import_term_mocks[ 1 ]->method( 'id' )
			->willReturn( $local_ids[ 1 ] );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'term', $local_ids[ 0 ] )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'term', $origin_ids[ 0 ] )
		);

		$testee->record_term( $import_term_mocks[ 0 ] );

		$this->assertSame(
			$origin_ids[ 0 ],
			$testee->origin_id( 'term', $local_ids[ 0 ] )
		);
		$this->assertSame(
			$local_ids[ 0 ],
			$testee->local_id( 'term', $origin_ids[ 0 ] )
		);

		$testee->record_term( $import_term_mocks[ 1 ] );

		$this->assertSame(
			$origin_ids[ 0 ],
			$testee->origin_id( 'term', $local_ids[ 0 ] )
		);
		$this->assertSame(
			$local_ids[ 0 ],
			$testee->local_id( 'term', $origin_ids[ 0 ] )
		);
		$this->assertSame(
			$origin_ids[ 1 ],
			$testee->origin_id( 'term', $local_ids[ 1 ] )
		);
		$this->assertSame(
			$local_ids[ 1 ],
			$testee->local_id( 'term', $origin_ids[ 1 ] )
		);
	}

	public function test_record_user() {

		$origin_id = 1;
		$local_id  = 2;

		$import_user_mock = $this->mock_builder->type_wp_import_user();
		$import_user_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_user_mock->method( 'id' )
			->willReturn( $local_id );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'user', $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'user', $origin_id )
		);

		$testee->record_user( $import_user_mock );

		$this->assertSame(
			$origin_id,
			$testee->origin_id( 'user', $local_id )
		);
		$this->assertSame(
			$local_id,
			$testee->local_id( 'user', $origin_id )
		);
	}


	public function test_record_comment() {

		$origin_id = 294;
		$local_id  = 233;

		$import_comment_mock = $this->mock_builder->type_wp_import_comment();
		$import_comment_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_comment_mock->method( 'id' )
			->willReturn( $local_id );

		$testee = new Data\ImportListeningTypeIdMapper;
		$type   = 'comment';

		$this->assertSame(
			0,
			$testee->origin_id( $type, $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( $type, $origin_id )
		);

		$testee->record_comment( $import_comment_mock );

		$this->assertSame(
			$origin_id,
			$testee->origin_id( $type, $local_id )
		);
		$this->assertSame(
			$local_id,
			$testee->local_id( $type, $origin_id )
		);
	}

	/**
	 * @dataProvider id_map_test_data
	 *
	 * @param array $map
	 * @param string $type
	 * @param strin $type_fqn
	 */
	public function test_id_map( Array $map, $type, $type_fqn ) {

		$testee     = new Data\ImportListeningTypeIdMapper;
		$origin_ids = [];
		$local_ids  = [];

		foreach ( $map as $pair ) {
			$origin_ids[] = $pair[ 'origin_id' ];
			$local_ids[]  = $pair[ 'local_id' ];
			$type_mock    = $this->getMockBuilder( $type_fqn )
				->getMock();
			$type_mock->method( 'origin_id' )
				->willReturn( $pair[ 'origin_id' ] );
			$type_mock->method( 'id' )
				->willReturn( $pair[ 'local_id' ] );
			call_user_func_array(
				[ $testee, "record_{$type}" ],
				[ $type_mock ]
			);
		}

		$map = array_combine( $origin_ids, $local_ids );
		$this->assertSame(
			$map,
			$testee->id_map( $type )
		);
	}

	/**
	 * @see test_id_map
	 * @return array
	 */
	public function id_map_test_data() {

		$data = [];

		$data[ 'comment' ] = [
			# 1. parameter $map,
			[
				[
					'origin_id' => 1,
					'local_id'  => 1,
				],
				[
					'origin_id' => 12,
					'local_id'  => 21
				],
				[
					'origin_id' => 42,
					'local_id'  => 24
				],
				[
					'origin_id' => 43,
					'local_id'  => 4
				]
			],
			# 2. parameter $type,
			'comment',
			# 3. parameter $type_fqn
			'W2M\Import\Type\ImportCommentInterface'
		];

		$data[ 'post' ] = [
			# 1. parameter $map,
			[
				[
					'origin_id' => 1,
					'local_id'  => 1,
				],
				[
					'origin_id' => 12,
					'local_id'  => 21
				],
				[
					'origin_id' => 42,
					'local_id'  => 24
				],
				[
					'origin_id' => 43,
					'local_id'  => 4
				]
			],
			# 2. parameter $type,
			'post',
			# 3. parameter $type_fqn
			'W2M\Import\Type\ImportPostInterface'
		];

		$data[ 'term' ] = [
			# 1. parameter $map,
			[
				[
					'origin_id' => 1,
					'local_id'  => 1,
				],
				[
					'origin_id' => 12,
					'local_id'  => 21
				],
				[
					'origin_id' => 42,
					'local_id'  => 24
				],
				[
					'origin_id' => 43,
					'local_id'  => 4
				]
			],
			# 2. parameter $type,
			'term',
			# 3. parameter $type_fqn
			'W2M\Import\Type\ImportTermInterface'
		];

		$data[ 'user' ] = [
			# 1. parameter $map,
			[
				[
					'origin_id' => 1,
					'local_id'  => 1,
				],
				[
					'origin_id' => 12,
					'local_id'  => 21
				],
				[
					'origin_id' => 42,
					'local_id'  => 24
				],
				[
					'origin_id' => 43,
					'local_id'  => 4
				]
			],
			# 2. parameter $type,
			'user',
			# 3. parameter $type_fqn
			'W2M\Import\Type\ImportUserInterface'
		];

		return $data;
	}

	/**
	 * @dataProvider suspend_side_effects_test_data
	 *
	 * @param array $comment
	 * @param array $post
	 * @param array $term
	 * @param array $user
	 */
	public function test_suspend_side_effects( Array $comment, Array $post, Array $term, Array $user ) {

		$comment_mock = $this->mock_builder->type_wp_import_comment();
		$comment_mock->method( 'origin_id' )
			->willReturn( $comment[ 'origin_id' ] );
		$comment_mock->method( 'id' )
			->willReturn( $comment[ 'local_id' ] );

		$post_mock = $this->mock_builder->type_wp_import_post();
		$post_mock->method( 'origin_id' )
			->willReturn( $post[ 'origin_id' ] );
		$post_mock->method( 'id' )
			->willReturn( $post[ 'local_id' ] );

		$term_mock = $this->mock_builder->type_wp_import_term();
		$term_mock->method( 'origin_id' )
			->willReturn( $term[ 'origin_id' ] );
		$term_mock->method( 'id' )
			->willReturn( $term[ 'local_id' ] );

		$user_mock = $this->mock_builder->type_wp_import_user();
		$user_mock->method( 'origin_id' )
			->willReturn( $user[ 'origin_id' ] );
		$user_mock->method( 'id' )
			->willReturn( $user[ 'local_id' ] );

		$testee = new Data\ImportListeningTypeIdMapper;
		$testee->record_comment( $comment_mock );
		$testee->record_post( $post_mock );
		$testee->record_term( $term_mock );
		$testee->record_user( $user_mock );

		$this->assertSame(
			$comment[ 'local_id' ],
			$testee->local_id( 'comment', $comment[ 'origin_id' ] )
		);
		$this->assertSame(
			$comment[ 'origin_id' ],
			$testee->origin_id( 'comment', $comment[ 'local_id' ] )
		);

		$this->assertSame(
			$post[ 'local_id' ],
			$testee->local_id( 'post', $post[ 'origin_id' ] )
		);
		$this->assertSame(
			$post[ 'origin_id' ],
			$testee->origin_id( 'post', $post[ 'local_id' ] )
		);

		$this->assertSame(
			$term[ 'local_id' ],
			$testee->local_id( 'term', $term[ 'origin_id' ] )
		);
		$this->assertSame(
			$term[ 'origin_id' ],
			$testee->origin_id( 'term', $term[ 'local_id' ] )
		);

		$this->assertSame(
			$user[ 'local_id' ],
			$testee->local_id( 'user', $user[ 'origin_id' ] )
		);
		$this->assertSame(
			$user[ 'origin_id' ],
			$testee->origin_id( 'user', $user[ 'local_id' ] )
		);
	}

	/**
	 * @see test_suspend_side_effects
	 * @return array
	 */
	public function suspend_side_effects_test_data() {

		$data = [];

		$data[ 'shared_origin_ids' ] = [
			# 1. parameter $comment
			[
				'origin_id' => 12,
				'local_id'  => 21
			],
			# 2. Parameter $post
			[
				'origin_id' => 12,
				'local_id'  => 22
			],
			# 3. Parameter $term
			[
				'origin_id' => 12,
				'local_id'  => 23
			],
			# 4. Parameter $user
			[
				'origin_id' => 12,
				'local_id'  => 24
			]
		];


		$data[ 'shared_local_ids' ] = [
			# 1. parameter $comment
			[
				'origin_id' => 12,
				'local_id'  => 21
			],
			# 2. Parameter $post
			[
				'origin_id' => 13,
				'local_id'  => 21
			],
			# 3. Parameter $term
			[
				'origin_id' => 14,
				'local_id'  => 21
			],
			# 4. Parameter $user
			[
				'origin_id' => 15,
				'local_id'  => 21
			]
		];

		$data[ 'shared_ids' ] = [
			# 1. parameter $comment
			[
				'origin_id' => 12,
				'local_id'  => 12
			],
			# 2. Parameter $post
			[
				'origin_id' => 12,
				'local_id'  => 12
			],
			# 3. Parameter $term
			[
				'origin_id' => 12,
				'local_id'  => 12
			],
			# 4. Parameter $user
			[
				'origin_id' => 12,
				'local_id'  => 12
			]
		];

		return $data;
	}
}
