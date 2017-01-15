<?php # -*- coding: utf-8 -*-

namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	WP_Http,
	Brain,
	DateTime;

class WpAttachmentImporterTest extends Helper\WpIntegrationTestCase {

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
	 * cleanup test uploads folder
	 */
	public function tearDown(){

		$wp_upload = wp_upload_dir();
		$wp_upload_subdir = array_reverse( array_filter( explode( '/', $wp_upload['subdir'] ) ) );

		array_map('unlink', glob( $wp_upload['basedir'] . $wp_upload['subdir'] . '/*' ) );

		foreach( $wp_upload_subdir as $i => $dir ){

			$parent_folder = FALSE;

			if( $i == 0 ){
				$i++;
				$parent_folder = $wp_upload_subdir[ $i ];
			}
			// Todo: this will fail when the directory is not empty
			rmdir( $wp_upload['basedir'] . '/'. $parent_folder . '/' . $dir );
		}

	}

	/**
	 * @group import
	 */
	public function test_import_post() {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();

		$testee = new Service\Importer\WpPostImporter( $id_mapper_mock, new WP_Http() );

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
		$origin_attachment_url = 'http://inpsyde.com/wp-content/themes/i/assets/img/logo.png';

		$postdata = array(
			'title'                 => $origin_attachment_url,
			'origin_author_id'      => 12,
			'status'                => 'draft',
			'guid'                  => $origin_attachment_url,
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
			'origin_attachment_url' => $origin_attachment_url
		);

		$new_parent_id = 15;
		$new_author_id = 1;

		$id_mapper_mock->expects( $this->atLeast( 2 ) )
		               ->method( 'local_id' )
		               ->withConsecutive(
			               array( 'post', $postdata[ 'origin_parent_post_id' ] ),
			               array( 'user', $postdata[ 'origin_author_id' ] )
		               )->will( $this->onConsecutiveCalls( $new_parent_id, $new_author_id ) );

		foreach ( $postdata as $method => $return_value ) {

			$post_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}

		$test_case = $this;
		$test_action = 'w2m_attachment_imported';

		$action_check = $this->getMockBuilder( 'ActionFiredTest' )
			->disableOriginalConstructor()
			->setMethods( [ 'action_fired' ] )
			->getMock();

		$action_check->expects( $this->exactly( 1 ) )
			->method( 'action_fired' )
			->with( $test_action );

		add_action(
			$test_action,
			function( $upload_data, $import_post ) use ( $test_case, $post_mock, $action_check ) {

				$action_check->action_fired( current_filter() );
				$this->assertInternalType(
					'array',
					$upload_data
				);

				$this->assertSame(
					$post_mock,
					$import_post
				);

				$this->assertFileExists(
					$upload_data[ 'file' ]
				);

				$this->assertSame(
					'image/png',
					$upload_data[ 'type' ]
				);

			},
			10,
			2
		);
		$testee->import_post( $post_mock );

	}

}
