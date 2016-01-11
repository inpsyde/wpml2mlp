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

		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\MultiTypeIdMapperInterface' )
		                       ->getMock();

		$testee = new Service\WpPostImporter( $translation_connector_mock, $id_mapper_mock );

		$post_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportPostInterface' )
		                  ->getMock();

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportPostInterface ) should return a proper value!
		 */
		$postdata = array(
			'post_title'            => 'Mocky test fight',
			'post_author'           => 2,
			'ping_status'           => 'draft',
			'guid'                  => 'mocky',
			'post_date'             => (new \DateTime( 'NOW' ))->format('Y-m-d H:i:s'),
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'origin_author_id'      => 42,
			'post_type'             => 'post',
			'post_excerpt'          => 'Mocky the fighter',
			'post_content'          => 'Mock will go for a greate fight.',
			'post_name'             => 'mocky',
			'post_parent'           => 42,
			'menu_order'            => 1,
			'post_password'         => 'mocky'
		);

		$postmeta = array(
			'is_sticky'             => FALSE,
			'origin_link'           => $postdata['guid'],
			'origin_parent_post_id' => $postdata['post_parent'],
			'terms'                 => array( 'terms' ),
			'meta'                  => array( 'meta' ),
			'locale_relations'      => array(
				'en_US' => 13,
				'fr_CH' => 32
			)
		);

		print_r( array_merge( $postdata, $postmeta ) );

		exit;

		#foreach ( $post_test_data as $method => $return_value ) {
		#	if ( 'locale_relations' === $method )
		#		continue; // we already have this one
#
		#	$post_mock->expects( $this->atLeast( 1 ) )
		#	               ->method( $method )
		#	               ->willReturn( $return_value );
#
		#}
#
		#$post_id = 3;
#
		#Brain\Monkey\Functions::expect( 'wp_insert_post' )
		#                      ->atLeast()->once()
		#                      ->with(
		#	                      $postdata,
		#	                      TRUE
		#                      )
		#                      ->andReturn( $post_id );
#
#
		#/**
		# * Remove this line when the test is completely configured.
		# * Currently the missing mock of wp_insert_post() lets the test
		# * ends in a fatal error.
		# */
		##$this->markTestIncomplete( 'Under Construction' );
		#$testee->import_post( $post_mock );

	}

}
