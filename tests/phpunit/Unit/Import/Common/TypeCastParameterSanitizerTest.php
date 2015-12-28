<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Common;

use
	W2M\Import\Common,
	DateTime,
	DateTimeZone,
	stdClass;

class TypeCastParameterSanitizerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider type_cast_test_data
	 *
	 * @param string $type
	 * @param mixed $value
	 * @param mixed $expected
	 */
	public function test_type_cast( $type, $value, $expected ) {

		$testee = new Common\TypeCastParameterSanitizer;
		$result = $testee->type_cast( $value, $type );

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @see test_type_cast
	 * @return array
	 */
	public function type_cast_test_data() {

		$data = array();

		$data[ 'integer_to_integer' ] = array(
			# 1.parameter $type
			'int',
			# 2. parameter $value
			42,
			# 3. parameter $expected
			42
		);

		$data[ 'string_to_string' ] = array(
			# 1.parameter $type
			'string',
			# 2. parameter $value
			'Lorem',
			# 3. parameter $expected
			'Lorem'
		);

		$data[ 'bool_to_bool' ] = array(
			# 1.parameter $type
			'bool',
			# 2. parameter $value
			TRUE,
			# 3. parameter $expected
			TRUE
		);

		$data[ 'float_to_float' ] = array(
			# 1.parameter $type
			'float',
			# 2. parameter $value
			42.12,
			# 3. parameter $expected
			42.12
		);

		$data[ 'array_to_array' ] = array(
			# 1.parameter $type
			'array',
			# 2. parameter $value
			array( 'foo' => 'bar' ),
			# 3. parameter $expected
			array( 'foo' => 'bar' )
		);

		$instance = (object) array(
			'bar' => 'bazz'
		);
		$data[ 'object_to_object' ] = array(
			# 1.parameter $type
			'object',
			# 2. parameter $value
			$instance,
			# 3. parameter $expected
			$instance
		);

		$data[ 'integer_to_string' ] = array(
			# 1.parameter $type
			'string',
			# 2. parameter $value
			42,
			# 3. parameter $expected
			'42'
		);

		$data[ 'float_to_string' ] = array(
			# 1.parameter $type
			'string',
			# 2. parameter $value
			42.12,
			# 3. parameter $expected
			'42.12'
		);

		$data[ 'string_to_integer' ] = array(
			# 1.parameter $type
			'int',
			# 2. parameter $value
			'42',
			# 3. parameter $expected
			42
		);

		$data[ 'float_string_to_integer' ] = array(
			# 1.parameter $type
			'int',
			# 2. parameter $value
			'42.42',
			# 3. parameter $expected
			42
		);

		$data[ 'string_to_float' ] = array(
			# 1.parameter $type
			'float',
			# 2. parameter $value
			'42.42',
			# 3. parameter $expected
			42.42
		);

		$data[ 'string_to_false' ] = array(
			# 1.parameter $type
			'bool',
			# 2. parameter $value
			'0',
			# 3. parameter $expected
			FALSE
		);

		$data[ 'string_to_true' ] = array(
			# 1.parameter $type
			'bool',
			# 2. parameter $value
			'1',
			# 3. parameter $expected
			TRUE
		);

		$data[ 'int_to_false' ] = array(
			# 1.parameter $type
			'bool',
			# 2. parameter $value
			0,
			# 3. parameter $expected
			FALSE
		);

		$data[ 'int_to_true' ] = array(
			# 1.parameter $type
			'bool',
			# 2. parameter $value
			1,
			# 3. parameter $expected
			TRUE
		);

		$data[ 'string_to_array' ] = array(
			# 1.parameter $type
			'array',
			# 2. parameter $value
			"Hello World!",
			# 3. parameter $expected
			array( "Hello World!" )
		);

		return $data;
	}

	/**
	 * @dataProvider sanitize_parameter_test_data
	 *
	 * @param array $mask
	 * @param array $parameter
	 * @param array $expected
	 */
	public function test_sanitize_parameter_strip_unknown(
		Array $mask,
		Array $parameter,
		Array $expected
	) {

		$testee = new Common\TypeCastParameterSanitizer( TRUE );
		$result = $testee->sanitize_parameter( $mask, $parameter );

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @dataProvider sanitize_parameter_test_data
	 *
	 * @param array $mask
	 * @param array $parameter
	 * @param array $unused
	 * @param array $expected
	 */
	public function test_sanitize_parameter_not_strip_unknown(
		Array $mask,
		Array $parameter,
		$unused,
		Array $expected
	) {

		$testee = new Common\TypeCastParameterSanitizer( FALSE );
		$result = $testee->sanitize_parameter( $mask, $parameter );

		$this->assertSame(
			$expected,
			$result
		);
	}

	/**
	 * @see test_sanitize_parameter_strip_unknown
	 * @return array
	 */
	public function sanitize_parameter_test_data() {

		$data = array();

		$data[ 'test_data' ] = array(
			# 1. Parameter $mask
			array(
				'id'            => 'int',
				'parent_id'     => 'int',
				'description'   => 'string',
				'price'         => 'float',
				'in_stock'      => 'bool',
				'comments_open' => 'bool',
				'ratings'       => 'array'
			),
			# 2. Parameter $parameter,
			array(
				'id'            => '42',
				'parent_id'     => '12',
				'description'   => 'Awesome Product',
				'price'         => '12.95',
				'in_stock'      => '1',
				'comments_open' => 0,
				'ratings'       => '5',
				'im_not_here'   => TRUE
			),
			# 3. parameter $expected_stripped
			array(
				'id'            => 42,
				'parent_id'     => 12,
				'description'   => 'Awesome Product',
				'price'         => 12.95,
				'in_stock'      => TRUE,
				'comments_open' => FALSE,
				'ratings'       => array( '5' )
			),
			# 4. parameter $expected_not_striped
			array(
				'id'            => 42,
				'parent_id'     => 12,
				'description'   => 'Awesome Product',
				'price'         => 12.95,
				'in_stock'      => TRUE,
				'comments_open' => FALSE,
				'ratings'       => array( '5' ),
				'im_not_here' => TRUE
			)
		);

		return $data;
	}

	/**
	 * @dataProvider sanitize_object_list_test_data
	 *
	 * @param array $object_list
	 * @param array $expected
	 */
	public function test_sanitize_object_list_strip_unknown( Array $object_list, Array $expected ) {

		$testee = new Common\TypeCastParameterSanitizer();
		$list = $testee->sanitize_object_list( $object_list, $expected[ 'type' ] );

		$this->assertCount(
			$expected[ 'count' ],
			$list
		);

		foreach ( $list as $key => $instance ) {
			$this->assertSame(
				$object_list[ $key ],
				$instance,
				"Instances are not the same for key '{$key}'"
			);
		}
	}

	/**
	 * @dataProvider sanitize_object_list_test_data
	 *
	 * @param array $object_list
	 * @param array $expected
	 */
	public function test_sanitize_object_list_not_strip_unknown( Array $object_list, Array $expected ) {

		$testee = new Common\TypeCastParameterSanitizer( FALSE );
		$list = $testee->sanitize_object_list( $object_list, $expected[ 'type' ] );

		$this->assertSame(
			$expected[ 'object_list_not_stripped' ],
			$list
		);
	}

	/**
	 * @see test_sanitize_object_list
	 * @return array
	 */
	public function sanitize_object_list_test_data() {

		$data = array();

		$object_list = array(
			new DateTime,
			new DateTime( '-3 days', new DateTimeZone( 'Europe/Berlin' ) ),
			new DateTime( '+4 minutes' )
		);

		$data[ 'only_date_time_list' ] = array(
			# 1. Parameter: $object_list
			$object_list,
			# 2. Parameter $expected
			array(
				'type'                     => 'DateTime',
				'count'                    => 3,
				'object_list_stripped'     => $object_list,
				'object_list_not_stripped' => $object_list,
			)
		);

		$object_list = array(
			new DateTime,
			new stdClass,
			new DateTime( '-3 days', new DateTimeZone( 'Europe/Berlin' ) ),
			new stdClass,
			new DateTime( '+4 minutes' )
		);


		$data[ 'mixed_date_time_list' ] = array(
			# 1. Parameter: $object_list
			$object_list,
			# 2. Parameter $expected
			array(
				'type'                     => 'DateTime',
				'count'                    => 3,
				'object_list_stripped'     => array(
					$object_list[ 0 ],
					$object_list[ 2 ],
					$object_list[ 4 ]
				),
				'object_list_not_stripped' => array(
					$object_list[ 0 ],
					NULL,
					$object_list[ 2 ],
					NULL,
					$object_list[ 4 ]
				)
			)
		);

		return $data;
	}
}
