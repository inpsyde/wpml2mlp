<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Importer;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain,
	DateTime;

class WpCommentImporterTest extends Helper\MonkeyTestCase {

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
		$this->getMock( 'WP_Comment' );

	}

	/**
	 * @group import
	 */
	public function test_import_comment() {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();

		$testee = new Service\Importer\WpCommentImporter( $id_mapper_mock );

		$comment_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportCommentInterface' )
		                     ->getMock();

		$wp_comment_mock = $this->getMock( 'WP_Comment' );

		$wp_error_update_comment_meta = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_error_update_comment_meta->method( 'add_data' )->with( '404' )->willReturn( "I've fallen and can't get up" );

		$commentmeta_mock = $this->mock_builder->type_wp_import_meta();
		$commentmeta_mock->method( 'key' )->willReturn( 'comment_meta_key' );
		$commentmeta_mock->method( 'value' )->willReturn( 'comment_meta_value' );
		$commentmeta_mock->method( 'is_single' )->willReturn( TRUE );

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportCommentInterface ) should return a proper value!
		 */
		$commentdata = array(
			'origin_user_id'            => 66,
			'author_name'               => 'Apollo Creed',
			'author_email'              => 'creed@apollo.com',
			'author_url'                => 'http://www.apollo-creed.com',
			'author_ip'                 => '777.999.0.1',
			'date'                      => new DateTime( 'NOW' ),
			'content'                   => 'Mocky you made it!',
			'karma'                     => 0,
			'approved'                  => 1,
			'agent'                     => 'Mozilla Haven sdk/45.566.7',
			'type'                      => 'spam',
			'origin_post_id'            => 13,
			'origin_parent_comment_id'  => 45,
			'meta'                      => array( $commentmeta_mock )
		);

		$comment_id    = 3;
		$new_parent_id = 15;
		$new_author_id = 3;
		$new_post_id   = 31;

		$id_mapper_mock->expects( $this->atLeast( 2 ) )
		               ->method( 'local_id' )
		               ->withConsecutive(
		                   array( 'comment', $commentdata[ 'origin_parent_comment_id' ] ),
		                   array( 'user', $commentdata[ 'origin_user_id' ] ),
		                   array( 'post', $commentdata[ 'origin_post_id' ] )
		               )->will( $this->onConsecutiveCalls( $new_parent_id, $new_author_id, $new_post_id ) );

		$comment = array(
			'comment_author'        => $commentdata['author_name'],
			'comment_author_email'  => $commentdata['author_email'],
			'comment_author_url'    => $commentdata['author_url'],
			'comment_author_IP'     => $commentdata['author_ip'],
			'comment_date_gmt'      => $commentdata['date']->format( 'Y-m-d H:i:s' ),
			'comment_content'       => $commentdata['content'],
			'comment_karma'         => $commentdata['karma'],
			'comment_approved'      => $commentdata['approved'],
			'comment_agent'         => $commentdata['agent'],
			'comment_type'          => $commentdata['type'],
			'comment_post_ID'       => $new_post_id,
			'comment_parent'        => $new_parent_id,
			'user_id'               => $new_author_id
		);


		foreach ( $commentdata as $method => $return_value ) {

			$comment_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}
		/**
		 * The ImportComment object has to receive the newly created id
		 */
		$comment_mock->expects( $this->once() )
			->method( 'id' )
			->with( $comment_id );

		Brain\Monkey\Functions::expect( 'wp_insert_comment' )
		                      ->atLeast()
		                      ->once()
		                      ->with( $comment )
		                      ->andReturn( $comment_id );

		Brain\Monkey\Functions::when( 'is_wp_error' )
		                      ->justReturn( FALSE );

		/**
		 * add_comment_meta ( @see ImportMetaInterface ).
		 */
		Brain\Monkey\Functions::expect( 'add_comment_meta' )->once();


		Brain\Monkey\Functions::expect( 'get_comment' )
		                      ->atLeast()
		                      ->once()
		                      ->with( $comment_id )
		                      ->andReturn( $wp_comment_mock );

		$testee->import_comment( $comment_mock );

	}

}
