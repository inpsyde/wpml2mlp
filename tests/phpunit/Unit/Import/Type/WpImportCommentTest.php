<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Type;

use
	W2M\Import\Type,
	W2M\Test\Helper,
	Brain,
	DateTime;

/**
 * @group import_comment
 */
class WpImportCommentTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider getter_test_data
	 *
	 * @param array $data
	 */
	public function test_id_no_value_set( Array $data ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_comment_id' )
			->never();

		$testee = new Type\WpImportComment( $data );
		$this->assertSame(
			0,
			$testee->id()
		);
	}

	/**
	 * @dataProvider getter_test_data
	 *
	 * @param array $data
	 */
	public function test_getter( Array $data ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_comment_id' )
			->never();

		$testee = new Type\WpImportComment( $data );

		foreach ( $data as $method => $value ) {
			$this->assertSame(
				$value,
				$testee->{$method}(),
				"Test failed for method '{$method}'"
			);
		}
	}

	/**
	 * @dataProvider getter_test_data
	 *
	 * @param array $data
	 */
	public function test_id_set_value( Array $data ) {

		$testee = new Type\WpImportComment( $data );
		Brain\Monkey::actions()
			->expectFired( 'w2m_import_set_comment_id' )
			->once()
			->with( $testee );

		$new_id = 85;
		$testee->id( $new_id );

		$this->assertSame(
			$new_id,
			$testee->id()
		);

		// id should be immutable
		$testee->id( 2 * $new_id );
		$this->assertSame(
			$new_id,
			$testee->id()
		);
	}

	/**
	 * @see test_getter
	 * @see test_id_no_value_set
	 * @return array
	 */
	public function getter_test_data() {

		$data = [];
		$data[ 'valid_comment' ] = [
			# 1. Parameter $data
			[
				'origin_id' => 42,
				'origin_post_id' => 953,
				'author_name' => 'John',
				'author_email' => 'john@doe.tld',
				'author_url' => 'https://doe.tld',
				'author_ip' => '127.0.0.1',
				'date' => new DateTime,
				'content' => 'Hi there, nice article!',
				'karma' => 10,
				'approved' => '1',
				'agent' => 'Mulder',
				'type' => 'comment',
				'origin_parent_comment_id' => 12,
				'origin_user_id' => 2
			]
		];

		return $data;
	}

	public function test_date_with_invalid_parameter() {

		$testee = new Type\WpImportComment(
			array( 'data' => '2016-01-12 21:00:00' )
		);

		$this->assertInstanceOf(
			'DateTime',
			$testee->date()
		);
	}

	public function test_consistent_types_with_no_data() {

		$testee = new Type\WpImportComment( array() );

		$string_returns = [
			'author_name', 'author_email', 'author_url', 'author_ip',
			'content', 'approved', 'agent', 'type'
		];
		$int_returns = [
			'origin_id', 'origin_post_id', 'karma', 'origin_user_id',
			'origin_parent_comment_id'
		];

		foreach ( $string_returns as $method ) {
			$this->assertInternalType(
				'string',
				$testee->{$method}(),
				"Test failed for method '{$method}'"
			);
		}

		foreach ( $int_returns as $method ) {
			$this->assertInternalType(
				'int',
				$testee->{$method}(),
				"Test failed for method '{$method}'"
			);
		}

		$this->assertInternalType(
			'array',
			$testee->meta()
		);
	}
}
