<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Type;

use
	W2M\Import\Type,
	W2M\Test\Helper,
	Brain;

class WpImportTermTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider term_test_data
	 * @param array $data
	 */
	public function test_id_no_value_set( Array $data ) {

		$testee = new Type\WpImportTerm( $data );

		// The action should not be fired
		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_term_id' )
			->never();
		// id should be 0 at the moment
		$id = $testee->id();
		$this->assertSame(
			0,
			$id
		);
	}

	/**
	 * @dataProvider term_test_data
	 * @param array $data
	 */
	public function test_id_set_value( Array $data ) {

		$testee = new Type\WpImportTerm( $data );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_term_id' )
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
	 * @dataProvider term_test_data
	 * @param array $data
	 */
	public function test_getter( Array $data ) {

		$testee = new Type\WpImportTerm( $data );
		foreach ( $data as $method => $value ) {
			$this->assertSame(
				$value,
				$testee->{$method}(),
				"Assertion failed for method '{$method}'"
			);
		}
	}

	/**
	 * @see test_id_no_value_set
	 * @return array
	 */
	public function term_test_data() {

		$data = array();

		$data[ 'general' ] = array(
			# 1.Parameter $data
			array(
				'origin_id'             => 42,
				'taxonomy'              => 'category',
				'name'                  => 'My Cat pics',
				'slug'                  => 'my-cat-pics',
				'description'           => 'Oh look at them…',
				'origin_parent_term_id' => 24,
				'locale_relations'      => array(
					'fr_BE' => 12,
					'en_US' => 14
				)
			)
		);

		return $data;
	}
}
