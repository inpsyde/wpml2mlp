<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper;

class WpPostImporterTest extends Helper\WpIntegrationTestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	public function setUp() {

		if ( ! $this->fs_helper ) {
			$this->fs_helper = new Helper\FileSystem;
		}

		parent::setUp();

	}

	/**
	 * @group import
	 */
	public function test_import_post() {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();

		$http = $this->getMockBuilder( 'WP_Http' )->disableOriginalConstructor()->getMock();

		$testee = new Service\WpPostImporter( $id_mapper_mock, $http );

		$post_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportPostInterface' )
		                  ->getMock();

		$postmeta_mock_single = $this->mock_builder->type_wp_import_meta();
		$postmeta_mock_single->method( 'key' )->willReturn( 'mocky' );
		$postmeta_mock_single->method( 'value' )->willReturn( 'mocky' );
		$postmeta_mock_single->method( 'is_single' )->willReturn( TRUE );

		$postmeta_mock_array = $this->mock_builder->type_wp_import_meta();
		$postmeta_mock_array->method( 'key' )->willReturn( 'mocky' );
		$postmeta_mock_array->method( 'value' )->willReturn( array( 'mocky', 'mreed' ) );
		$postmeta_mock_array->method( 'is_single' )->willReturn( FALSE );

		$term_mock = $this->mock_builder->type_wp_term_reference();
		$term_mock->method( 'origin_id' )->willReturn( 113 );
		$term_mock->method( 'taxonomy' )->willReturn( 'category' );

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportPostInterface ) should return a proper value!
		 */
		$postdata = array(
			'title'                 => 'Mocky test fight',
			'origin_author_id'      => 12,
			'status'                => 'draft',
			'guid'                  => 'mocky',
			'date'                  => ( new \DateTime( 'NOW' ) )->format( 'Y-m-d H:i:s' ),
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'type'                  => 'post',
			'excerpt'               => 'Mocky the fighter',
			'content'               => 'Mock will go for a greate fight.',
			'name'                  => 'mocky',
			'origin_parent_post_id' => 42,
			'menu_order'            => 1,
			'password'              => 'mocky',
			'is_sticky'             => TRUE,
			'origin_link'           => 'http://wpml2mlp.test/mocky',
			'terms'                 => array( $term_mock ),
			'meta'                  => array( $postmeta_mock_single, $postmeta_mock_array ),

		);

		foreach ( $postdata as $method => $return_value ) {

			$post_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}

		$new_parent_id = 15;
		$new_author_id = 1;

		$id_mapper_mock->expects( $this->atLeast( 2 ) )
		               ->method( 'local_id' )
		               ->withConsecutive(
			               array( 'post', $postdata[ 'origin_parent_post_id' ] ),
			               array( 'user', $postdata[ 'origin_author_id' ] )
		               )->will( $this->onConsecutiveCalls( $new_parent_id, $new_author_id ) );

		$testee->import_post( $post_mock );

	}

}
