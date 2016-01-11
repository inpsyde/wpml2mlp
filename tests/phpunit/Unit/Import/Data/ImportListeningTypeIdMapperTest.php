<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Data;

use
	W2M\Test\Helper,
	W2M\Import\Data;

class ImportListeningTypeIdMapperTest extends Helper\MonkeyTestCase {

	public function test_record_post() {

		$origin_id = 124;
		$local_id  = 523;

		$wp_post_mock = $this->mock_builder->wp_post();
		$wp_post_mock->ID = $local_id;

		$import_post_mock = $this->mock_builder->type_wp_import_post();
		$import_post_mock->method( 'origin_id' )
			->willReturn( $origin_id );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'post', $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'post', $origin_id )
		);

		$testee->record_post( $wp_post_mock, $import_post_mock );

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

		$origin_id = 465;
		$local_id  = 632;

		$wp_term_mock = $this->mock_builder->wp_term();
		$wp_term_mock->term_id = $local_id;

		$import_term_mock = $this->mock_builder->type_wp_import_term();
		$import_term_mock->method( 'origin_id' )
			->willReturn( $origin_id );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'term', $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'term', $origin_id )
		);

		$testee->record_term( $wp_term_mock, $import_term_mock );

		$this->assertSame(
			$origin_id,
			$testee->origin_id( 'term', $local_id )
		);
		$this->assertSame(
			$local_id,
			$testee->local_id( 'term', $origin_id )
		);
	}

	public function test_record_user() {

		$origin_id = 1;
		$local_id  = 2;

		$wp_user_mock = $this->mock_builder->wp_user();
		$wp_user_mock->ID = $local_id;

		$import_user_mock = $this->mock_builder->type_wp_import_user();
		$import_user_mock->method( 'origin_id' )
			->willReturn( $origin_id );

		$testee = new Data\ImportListeningTypeIdMapper;

		$this->assertSame(
			0,
			$testee->origin_id( 'user', $local_id )
		);
		$this->assertSame(
			0,
			$testee->local_id( 'user', $origin_id )
		);

		$testee->record_user( $wp_user_mock, $import_user_mock );

		$this->assertSame(
			$origin_id,
			$testee->origin_id( 'user', $local_id )
		);
		$this->assertSame(
			$local_id,
			$testee->local_id( 'user', $origin_id )
		);
	}
}
