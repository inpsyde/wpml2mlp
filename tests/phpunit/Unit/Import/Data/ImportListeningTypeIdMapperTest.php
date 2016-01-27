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
}
