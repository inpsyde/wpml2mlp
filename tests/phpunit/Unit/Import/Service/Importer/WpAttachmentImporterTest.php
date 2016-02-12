<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Importer;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain,
	Mockery,
	DateTime;

class WpAttachmentImporterTest extends Helper\MonkeyTestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	public function setUp() {

		if ( ! $this->fs_helper ) {
			$this->fs_helper = new Helper\FileSystem;
		}

		parent::setUp();

		/**
		 * Just create some mocks of these types to avoid
		 * error messages like this
		 * https://github.com/sebastianbergmann/phpunit-mock-objects/issues/273
		 * when mocking objects that type hint WP core components
		 */
		$this->getMock( 'WP_Post' );

	}

	/**
	 * @group import
	 */
	public function test_import_post() {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();

		$http_mock = $this->getMockBuilder( 'WP_Http' )
		                  ->disableOriginalConstructor()
		                  ->setMethods( array( 'request' ) )
		                  ->getMock();

		$testee = new Service\Importer\WpPostImporter( $id_mapper_mock, $http_mock );

		$post_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportPostInterface' )
		                  ->getMock();

		$wp_error_update_post_meta = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_error_update_post_meta->method( 'add_data' )->with( '404' )->willReturn( "I've fallen and can't get up" );

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
			'date'                  => new DateTime( 'NOW' ),
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'type'                  => 'attachment',
			'excerpt'               => 'Mocky the fighter',
			'content'               => 'Mock will go for a greate fight.',
			'name'                  => 'mocky',
			'origin_parent_post_id' => 42,
			'menu_order'            => 1,
			'password'              => 'mocky',
			'origin_link'           => 'http://wpml2mlp.test/mocky',
			'terms'                 => array( $term_mock ),
			'meta'                  => array( $postmeta_mock_single, $postmeta_mock_array ),
			'origin_attachment_url' => 'https://images.unsplash.com/photo-1444858345149-8ff40887589b?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&s=1b5d1a032e0bc68e2bf514e1e348c138'
		);

		$wp_upload_dir_data = array(
			'basedir' =>
				str_replace(
					'plugins/wpml2mlp/tests/phpunit/Unit/Import/Service',
					'upload/wpml2mlp_test',
					dirname( __FILE__ )
				),
			'baseurl' => 'http://mocky.test',
			'subdir' => date( '/Y/m', time() )
		);

		$wp_upload_file = $wp_upload_dir_data['basedir'] . $wp_upload_dir_data['subdir'] . '/'. basename( $postdata[ 'origin_attachment_url' ] );


		$post_id = 3;
		$new_parent_id = 15;
		$new_author_id = 3;

		$id_mapper_mock->expects( $this->atLeast( 2 ) )
		               ->method( 'local_id' )
		               ->withConsecutive(
			               array( 'post', $postdata[ 'origin_parent_post_id' ] ),
			               array( 'user', $postdata[ 'origin_author_id' ] )
		               )->will( $this->onConsecutiveCalls( $new_parent_id, $new_author_id ) );

		$post = array(
			'post_title'     => $postdata[ 'title' ],
			'post_author'    => $new_author_id,
			'post_status'    => $postdata[ 'status' ],
			'guid'           => $postdata[ 'guid' ],
			'post_date_gmt'  => $postdata[ 'date' ]->format( 'Y-m-d H:i:s'),
			'comment_status' => $postdata[ 'comment_status' ],
			'ping_status'    => $postdata[ 'ping_status' ],
			'post_type'      => $postdata[ 'type' ],
			'post_excerpt'   => $postdata[ 'excerpt' ],
			'post_content'   => $postdata[ 'content' ],
			'post_name'      => $postdata[ 'name' ],
			'post_parent'    => $new_parent_id,
			'menu_order'     => $postdata[ 'menu_order' ],
			'post_password'  => $postdata[ 'password' ]
		);

		foreach ( $postdata as $method => $return_value ) {

			$post_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}

		Brain\Monkey\Functions::expect( 'wp_insert_attachment' )
		                      ->atLeast()
		                      ->once()
		                      ->with(
			                      $post,
			                      $postdata[ 'origin_attachment_url' ],
			                      $new_parent_id
		                      )
		                      ->andReturn( $post_id );

		Brain\Monkey\Functions::when( 'is_wp_error' )
		                      ->justReturn( FALSE );

		$post_return = array(
			'ID'                    => $post_id,
			'to_ping'               => FALSE,
			'pinged'                => FALSE,
			'post_content_filtered' => FALSE,
			'post_mime_type'        => FALSE,
			'comment_count'         => 0,
			'filter'                => 'raw',
			'ancestors'             => array( 42 ),
			'post_category'         => array( 1 ),
			'tags_input'            => array()
		);

		$post_return = array_merge( $post, $post_return );

		Brain\Monkey\Functions::expect( 'get_post' )
		                      ->atLeast()
		                      ->once()
		                      ->with( $post_id )
		                      ->andReturn( $post_return );



		Brain\Monkey\Functions::expect( 'wp_set_post_terms' )->once();


		/**
		 * update_post_meta needs expect 2 times.
		 * At first save the _w2m_origin_link.
		 * The second looped $post->meta() ( @see ImportPostInterface )
		 */
		Brain\Monkey\Functions::expect( 'update_post_meta' )->times( 2 );

		/**
		 * Is a postmeta value type array we have to add the Postmeta at the same metakey
		 * @see $postmeta_mock_array
		 */
		Brain\Monkey\Functions::expect( 'add_post_meta' )->twice();


		Brain\Monkey\Functions::expect( 'wp_check_filetype' )
		                      ->atLeast()
		                      ->once()
		                      ->with( basename( $postdata[ 'origin_attachment_url' ] ), null )
		                      ->andReturn( array(
			                                   'ext' => 'jpg',
			                                   'type' => 'image/jpeg'
		                                   )
		                      );

		Brain\Monkey\Functions::expect( 'wp_upload_dir' )
		                      ->atLeast()
		                      ->once()
		                      ->andReturn( $wp_upload_dir_data );

		Brain\Monkey\Functions::expect( 'wp_upload_bits' )
		                      ->once()
		                      ->with(
		                          basename( $postdata[ 'origin_attachment_url' ] ),
		                          0,
		                          '',
		                          "{$postdata['date']->format('Y')}/{$postdata['date']->format('m')}"
		                      )
		                      ->andReturn( array(
			                                   'file'   => $wp_upload_file,
			                                   'url'    => $wp_upload_dir_data['baseurl'] . '/wp-content/' . $wp_upload_dir_data['basedir'] . $wp_upload_dir_data['subdir'] . '/'. basename( $postdata[ 'origin_attachment_url' ] ),
			                                   'type'   => 'image/jpeg',
			                                   'error'  => false
		                                   )
		                      );

		/**
		 * Testdata for a valid request response
		 */
		$request_testdata = array(
								'headers' => array( 'content-type' => 'image/jpeg' ),
								'body' => 'imagesource',
								'response' => array(
													'code' => 200,
													'message' => 'OK',
                                                ),
								'cookies' => array(),
								'filename' => FALSE,
							);

		$http_mock->expects( $this->atLeast( 1 ) )
		          ->method( 'request' )
		          ->with( $postdata[ 'origin_attachment_url' ] )
		          ->willReturn( $request_testdata );


		Brain\Monkey\Functions::expect( 'wp_update_post' )->times( 1 );

		Brain\Monkey\Functions::expect( 'wp_generate_attachment_metadata' )
		                      ->atLeast()
		                      ->once()
		                      ->with( $post_id, $wp_upload_file )
		                      ->andReturn( array(
			                                   array( 'metadata' ), $post_id
		                                   )
		                      );

		Brain\Monkey\Functions::expect( 'wp_update_attachment_metadata' )
		                      ->atLeast()
		                      ->once();

		$testee->import_post( $post_mock );

	}

}
