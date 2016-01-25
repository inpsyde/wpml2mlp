<?php # -*- coding: utf-8 -*-
namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper;

class WpCommentImporterTest extends Helper\WpIntegrationTestCase {

	/**
	 * @group import_comment
	 */
	public function test_import_comment() {

		$comment_mock = $this->mock_builder->type_wp_import_comment();

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();

		$testee = new Service\WpCommentImporter( $id_mapper_mock );


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
			'date'                      => ( new \DateTime( 'NOW' ) )->format( 'Y-m-d H:i:s' ),
			'content'                   => 'Mocky you made it!',
			'karma'                     => 0,
			'approved'                  => 1,
			'agent'                     => 'Mozilla Haven sdk/45.566.7',
			'type'                      => 'spam',
			'origin_post_id'            => 13,
			'origin_parent_comment_id'  => 45,
			'meta'                      => array( $commentmeta_mock )
		);

		$comment_id = 3;
		$new_parent_id = 15;
		$new_author_id = 3;

		$id_mapper_mock->expects( $this->atLeast( 2 ) )
		               ->method( 'local_id' )
		               ->withConsecutive(
			               array( 'comment', $commentdata[ 'origin_parent_comment_id' ] ),
			               array( 'user', $commentdata[ 'origin_user_id' ] )
		               )->will( $this->onConsecutiveCalls( $new_parent_id, $new_author_id ) );

		$comment = array(
			'comment_author'        => $new_author_id,
			'comment_author_email'  => $commentdata['author_name'],
			'comment_author_url'    => $commentdata['author_email'],
			'comment_author_IP'     => $commentdata['author_url'],
			'comment_date'          => $commentdata['author_ip'],
			'comment_date_gmt'      => $commentdata['date'],
			'comment_content'       => $commentdata['content'],
			'comment_karma'         => $commentdata['karma'],
			'comment_approved'      => $commentdata['approved'],
			'comment_agent'         => $commentdata['agent'],
			'comment_type'          => $commentdata['type'],
			'comment_post_ID'       => $commentdata['origin_post_id'],
			'comment_parent'        => $new_parent_id,
		);

		foreach ( $commentdata as $method => $return_value ) {

			$comment_mock->expects( $this->atLeast( 1 ) )
			             ->method( $method )
			             ->willReturn( $return_value );

		}

		$testee->import_comment( $comment_mock );

	}
}