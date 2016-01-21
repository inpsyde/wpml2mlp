<?php # -*- coding: utf-8 -*-
namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	PHPUnit_Framework_TestCase;

class WpUserImporterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group import_user
	 */
	public function test_import_user() {

		$this->markTestSkipped( 'Under construction…' );


		$user_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportUserInterface' )
		                  ->getMock();

		$testee = new Service\WpUserImporter();

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportUserInterface ) should return a proper value!
		 */
		$user_test_data = array(
			'login'         => 'mocky',
			'email'         => 'mocky@wordpress.com',
			'first_name'    => 'Mocky',
			'last_name'     => 'Walboa',
			'display_name'  => 'Mocky the tester'
		);

		$user = array(
			'user_login'    => $user_test_data[ 'login' ],
			'user_email'    => $user_test_data[ 'email' ],
			'first_name'    => $user_test_data[ 'first_name' ],
			'last_name'     => $user_test_data[ 'last_name' ],
			'display_name'  => $user_test_data[ 'display_name' ]
		);

		$origin_user_id = 3;
		$local_user_id = 15;

		foreach ( $user_test_data as $method => $return_value ) {

			$user_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}



		$testee->import_user( $user_mock );

	}
}