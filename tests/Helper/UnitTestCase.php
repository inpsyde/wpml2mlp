<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	PHPUnit_Framework_TestCase;

class UnitTestCase extends PHPUnit_Framework_TestCase {

	/**
	 * @var MockBuilder
	 */
	protected $mock_builder;

	public function setUp() {

		parent::setUp();
		if ( $this->mock_builder )
			return;

		$this->mock_builder = new MockBuilder( $this );
	}
}