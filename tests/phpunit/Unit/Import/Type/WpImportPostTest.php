<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Type;

use
	W2M\Import\Type,
	W2M\Test\Helper,
	Brain;

class WpImportPostTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider post_test_data
	 *
	 * @param array $data
	 */
	public function test_id_no_value_set( Array $data ) {

		$testee = new Type\WpImportPost( $data );

		// This action must not be fired
		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_post_id' )
			->never();
		// id should be 0 at this moment
		$id = $testee->id();
		$this->assertSame(
			0,
			$id
		);
	}

	/**
	 * @dataProvider post_test_data
	 *
	 * @param array $data
	 */
	public function test_getter( Array $data ) {

		$testee = new Type\WpImportPost( $data );

		foreach ( $data as $method => $expected ) {
			$this->assertSame(
				$expected,
				$testee->{$method}(),
				"Assertion failed for method '{$method}'"
			);
		}
		$this->markTestIncomplete( 'Implement date() method' );
	}

	/**
	 * @dataProvider post_test_data
	 *
	 * @param array $data
	 */
	public function test_id_set_value( Array $data ) {

		$testee = new Type\WpImportPost( $data );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_post_id' )
			->once()
			->with( $testee );

		$this->assertSame(
			0,
			$testee->id()
		);

		// set an ID
		$test_id = 1;
		$testee->id( $test_id );

		$this->assertSame(
			$test_id,
			$testee->id()
		);

		// test that the ID can not be changed
		$testee->id( $test_id + $test_id );
		$this->assertSame(
			$test_id,
			$testee->id()
		);
	}


	/**
	 * @see test_getter
	 * @return array
	 */
	public function post_test_data() {

		$data = array();

		$data[ 'general' ] = array(
			# 1. Parameter $data
			array(
				'origin_id'             => 7239,
				'title'                 => 'Import Page',
				'guid'                  => 'http://import.dev/?p=7239',
				'date'                  => new \DateTime( 'now', new \DateTimeZone( 'UTC' ) ),
				'comment_status'        => 'open',
				'ping_status'           => 'close',
				'type'                  => 'page',
				'is_sticky'             => FALSE,
				'origin_link'           => 'http://import.dev/import-page',
				'excerpt'               => "Some cool\nExcerpt in here…",
				'content'               => "Lorem ipsum dolor sit\n\namet sit dolor ipsum lorem.",
				'name'                  => 'import-page',
				'origin_parent_post_id' => 7228,
				'menu_order'            => 0,
				'password'              => 'Top Secret!',
				'terms'                 => array( '//Todo' ),
				'meta'                  => array( '//Todo' ),
				'locale_relations'      => array(
					'en_GB' => 422,
					'fr_BE' => 45
				)
			)
		);

		return $data;
	}
}
