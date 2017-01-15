<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Common;

use
	W2M\Test\Helper;

class ConsecutiveParameterExampleTest extends Helper\MonkeyTestCase {

	/**
	 * This test just shows an example on how to build a mock with
	 * consecutive method calls.
	 */
	public function test_mock() {

		$mock = $this->getMockBuilder( 'Foo' )
			->setMethods( [ 'do_something' ] )
			->getMock();

		$mock->method( 'do_something' )
			->withConsecutive(
				[ $this->equalTo( 1 ) ],
				[ $this->equalTo( 2 ) ],
				[ $this->equalTo( 3 ) ]
			)
			->willReturnArgument( 0 );

		$this->assertSame( 1, $mock->do_something( 1 ) );
		$this->assertSame( 2, $mock->do_something( 2 ) );
		$this->assertSame( 3, $mock->do_something( 3 ) );
	}
}