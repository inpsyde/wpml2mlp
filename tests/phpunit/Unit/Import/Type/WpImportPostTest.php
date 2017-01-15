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

		/**
		 * it seems that the data provider is called before the setUp() method of the parent
		 */
		if ( ! $this->mock_builder )
			$this->mock_builder = new Helper\MockBuilder( $this );

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
				'status'                => 'draft',
				'origin_parent_post_id' => 7228,
				'menu_order'            => 0,
				'password'              => 'Top Secret!',
				'terms'                 => array( $this->mock_builder->type_wp_term_reference() ),
				'meta'                  => array( $this->mock_builder->type_wp_import_meta() ),
				'locale_relations'      => array( $this->mock_builder->type_locale_relation() ),
				'origin_attachment_url' => 'https://images.unsplash.com/photo-1444858345149-8ff40887589b?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&s=1b5d1a032e0bc68e2bf514e1e348c138'
			)
		);

		return $data;
	}

	public function test_date_invalid_parameter() {

		$testee = new Type\WpImportPost(
			// Note: the date is not meant to be parsed, and thus we don't test it, and just check the proper type
			array( 'date' => '2015-10-10' )
		);

		$this->assertInstanceOf(
			'DateTime',
			$testee->date()
		);
	}

	public function test_consistent_types_with_no_data() {

		$testee = new Type\WpImportPost( array() );

		$string_returns = array(
			'title', 'guid', 'comment_status', 'ping_status', 'type', 'origin_link',
			'excerpt', 'content', 'name', 'status', 'password'
		);
		foreach ( $string_returns as $method ) {
			$this->assertInternalType(
				'string',
				$testee->{$method}(),
				"Test failed for method {$method}"
			);
		}

		$int_returns = array(
			'id', 'origin_id', 'origin_parent_post_id', 'menu_order'
		);
		foreach ( $int_returns as $method ) {
			$this->assertInternalType(
				'int',
				$testee->{$method}(),
				"Test failed for method {$method}"
			);
		}

		$array_returns = array( 'terms', 'meta', 'locale_relations' );
		foreach ( $array_returns as $method ) {
			$this->assertInternalType(
				'array',
				$testee->{$method}(),
				"Test failed for {$method}"
			);
		}

		$this->assertInternalType(
			'bool',
			$testee->is_sticky()
		);
		$this->assertInstanceOf(
			'DateTime',
			$testee->date()
		);
	}
}
