<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	WP_UnitTestCase;

class WpIntegrationTestCase extends WP_UnitTestCase {

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