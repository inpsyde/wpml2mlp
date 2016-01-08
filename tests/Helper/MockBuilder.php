<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	PHPUnit_Framework_TestCase,
	PHPUnit_Framework_MockObject_MockObject;

class MockBuilder {

	/**
	 * @var PHPUnit_Framework_TestCase
	 */
	private $test_case;

	/**
	 * @param PHPUnit_Framework_TestCase $test_case
	 */
	public function __construct( PHPUnit_Framework_TestCase $test_case ) {

		$this->test_case = $test_case;
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_meta( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpImportMeta', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_term_reference( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpTermReference', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_error( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_Error', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function common_wp_factory( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Common\WpFactory',
			$methods
		);
	}

	/**
	 * @param $class
	 * @param array $methods
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function mock_without_constructor( $class, Array $methods = array() ) {

		$mockBuilder = $this->test_case->getMockBuilder( $class )
			->disableOriginalConstructor();
		if ( $methods )
			$mockBuilder->setMethods( $methods );

		return $mockBuilder->getMock();
	}
}