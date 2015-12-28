<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	Brain\Monkey,
	PHPUnit_Framework_TestCase;

/**
 * Class MonkeyTestCase
 *
 * Wraps Brain\Monkey setup
 *
 * @package W2M\Test\Helper
 */
class MonkeyTestCase extends PHPUnit_Framework_TestCase {

	public function setUp() {
		parent::setUp();
		Monkey::setUp();
		Monkey::setUpWP();
	}

	public function tearDown() {
		parent::tearDown();
		Monkey::tearDown();
		Monkey::tearDownWP();
	}
}