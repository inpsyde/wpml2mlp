<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use Brain;
use W2M\Import\Service;
use W2M\Test\Helper;

class WpPostImporterTest extends \PHPUnit_Framework_TestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	protected function setUp() {

		if ( !$this->fs_helper ) {
			$this->fs_helper = new Helper\FileSystem;
		}

		Brain\Monkey::setUp();
		Brain\Monkey::setUpWP();

		/**
		 * Just create some mocks of these types to avoid
		 * error messages like this
		 * https://github.com/sebastianbergmann/phpunit-mock-objects/issues/273
		 * when mocking objects that type hint WP core components
		 */
		$this->getMock( 'WP_Post' );
	}

	/**
	 * runs after each test
	 */
	protected function tearDown() {

		Brain\Monkey::tearDown();
		Brain\Monkey::tearDownWP();
	}


	/**
	 * @group import_post
	 */
	public function test_import_post() {

		/**
		 * Create mocks for the dependency of the testee (WpPostImporter)
		 */
		$translation_connector_mock = $this->getMockBuilder( 'W2M\Import\Service\TranslationConnectorInterface' )
		                                   ->getMock();

		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\IdMapperInterface' )
		                       ->getMock();

		$testee = new Service\WpPostImporter( $translation_connector_mock, $id_mapper_mock );

		$post_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportPostInterface' )
		                  ->getMock();

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportPostInterface ) should return a proper value!
		 */
		$post_test_data = array(
			'title'                 => 'Mocky test fight',
			'status'                => 'draft',
			'guid'                  => 'mocky',
			'date'                  => (new \DateTime( 'NOW' ))->format('Y-m-d H:i:s'),
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'origin_author_id'      => 42,
			'type'                  => 'post',
			'is_sticky'             => FALSE,
			'origin_link'           => 'mocky',
			'excerpt'               => 'Mocky the fighter',
			'content'               => 'Mock will go for a greate fight.',
			'name'                  => 'mocky',
			'origin_parent_post_id' => 42,
			'menu_order'            => 1,
			'password'              => 'mocky',
			'terms'                 => array( 'terms' ),
			'meta'                  => array( 'meta' ),
			'locale_relations'      => array(
				'en_US' => 13,
				'fr_CH' => 32
			)
		);


		foreach ( $post_test_data as $method => $return_value ) {
			if ( 'locale_relations' === $method )
				continue; // we already have this one

			$post_mock->expects( $this->atLeast( 1 ) )
			               ->method( $method )
			               ->willReturn( $return_value );

		}

		/**
		 * Remove this line when the test is completely configured.
		 * Currently the missing mock of wp_insert_post() lets the test
		 * ends in a fatal error.
		 */
		$this->markTestIncomplete( 'Under Construction' );
		$testee->import_post( $post_mock );

	}

}
