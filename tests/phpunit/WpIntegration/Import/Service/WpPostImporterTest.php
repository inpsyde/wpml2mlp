<?php # -*- coding: utf-8 -*-

namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	W2M\Import\Type,
	W2M\Test\Helper,
	WP_Post,
	DateTime;

class WpPostImporterTest extends Helper\WpIntegrationTestCase {

	/**
	 * @group import
	 * @dataProvider import_post_test_data
	 *
	 * @param array $post_data
	 * @param array $meta_data
	 */
	public function test_import_post( Array $post_data, Array $meta_data, Array $id_map ) {

		$id_mapper_mock = $this->build_id_map_mock( $post_data, $id_map );
		$http_mock      = $this->mock_builder->wp_http();
		$http_mock->expects( $this->never() )
			->method( 'request' );

		$import_post_mock = $this->build_import_post_mock( $post_data, $meta_data, [] );

		/**
		 * Testing that the action actually gets triggered:
		 * Create a anonymous mock and expect a method to be
		 * called exactly once.
		 */
		$text_action  = 'w2m_post_imported';
		$action_check = $this->getMockBuilder( 'ActionFiredTest' )
			->disableOriginalConstructor()
			->setMethods( [ 'action_fired' ] )
			->getMock();
		$action_check->expects( $this->exactly( 1 ) )
			->method( 'action_fired' )
			->with( $text_action );

		add_action(
			$text_action,
			/**
			 * @param WP_Post $wp_post
			 * @param Type\ImportPostInterface $import_post
			 */
			function( $wp_post, $import_post )
				use ( $action_check, $import_post_mock, $meta_data, $id_map )
			{
				$action_check->action_fired( current_filter() );
				$this->assertInstanceOf(
					'WP_Post',
					$wp_post
				);
				$this->assertSame(
					$import_post_mock,
					$import_post
				);
				$this->make_post_assertions( $wp_post, $import_post, $id_map );
				$this->make_meta_assertions( $wp_post, $meta_data );
			},
			10,
			2
		);

		$testee = new Service\WpPostImporter( $id_mapper_mock, $http_mock );
		$testee->import_post( $import_post_mock );
	}

	/**
	 * Compare imported post with import data
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 * @param array $id_map
	 */
	public function make_post_assertions(
		WP_Post $wp_post,
		Type\ImportPostInterface $import_post,
		Array $id_map
	) {

		$this->assertSame(
			$import_post->title(),
			$wp_post->post_title
		);
		$this->assertSame(
			$import_post->guid(),
			$wp_post->guid
		);
		$this->assertSame(
			$import_post->origin_link(),
			$wp_post->_w2m_origin_link // gets post meta
		);

		$post_keys = [ 'type', 'name', 'status', 'excerpt', 'content', 'password' ];
		foreach ( $post_keys as $key ) {
			$this->assertSame(
				$import_post->{$key}(),
				$wp_post->{"post_{$key}"},
				"Test failed for key post_{$key}"
			);
		}

		$this->assertSame(
			$import_post->comment_status(),
			$wp_post->comment_status
		);
		$this->assertSame(
			$import_post->ping_status(),
			$wp_post->ping_status
		);
		$this->assertSame(
			$import_post->menu_order(),
			$wp_post->menu_order
		);

		$this->assertSame(
			$import_post->date()->format( 'Y-m-d H:i:s' ),
			$wp_post->post_date_gmt
		);

		$this->assertSame(
			$id_map[ 'post_parent_id' ],
			(int) $wp_post->post_parent
		);
		$this->assertSame(
			$id_map[ 'post_author_id' ],
			(int) $wp_post->post_author
		);
	}

	/**
	 * Compare imported post meta with import data
	 *
	 * @param WP_Post $post
	 * @param array $meta_data
	 */
	public function make_meta_assertions( WP_Post $post, Array $meta_data ) {

		foreach ( $meta_data as $record ) {
			$this->assertSame(
				$record[ 'value' ],
				get_post_meta( $post->ID, $record[ 'key' ], $record[ 'is_single' ] ),
				"Test failed for key {$record[ 'key' ]}"
			);
		}
	}

	/**
	 * compare post terms with import data
	 *
	 * @param WP_Post $post
	 * @param array $term_data
	 */
	public function make_term_assertions( WP_Post $post, Array $term_data ) {

		//Todo: Make tests to verify data consistency
	}

	/**
	 * @param array $post_data
	 * @param array $meta_data
	 * @param $term_data
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function build_import_post_mock( Array $post_data, Array $meta_data, $term_data ) {

		$post_data[ 'terms' ] = [];
		$post_data[ 'meta'  ] = [];
		foreach ( $meta_data as $meta_record ) {
			$post_data[ 'meta' ][] = $this->mock_builder->type_wp_import_meta( [], $meta_record );
		}

		return $this->mock_builder->type_wp_import_post( [], $post_data );
	}

	/**
	 * @param array $post_data
	 * @param array $id_map
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function build_id_map_mock( Array $post_data, Array $id_map ) {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();
		$id_mapper_mock
			->expects( $this->atLeast( 2 ) )
			->method( 'local_id' )
			->withConsecutive(
				array( 'post', $post_data[ 'origin_parent_post_id' ] ),
				array( 'user', $post_data[ 'origin_author_id' ] )
			)
			->will(
				$this->onConsecutiveCalls(
					$id_map[ 'post_parent_id'],
					$id_map[ 'post_author_id' ]
				)
			);

		return $id_mapper_mock;
	}

	/**
	 * @see test_import_post
	 *
	 * @return array
	 */
	public function import_post_test_data() {

		$data = [];

		$data[ 'test_1' ] = [
			# 1. parameter $post_data
			[
				'origin_id'             => 4923,
				'title'                 => 'Mocky test fight',
				'origin_author_id'      => 2,
				'status'                => 'draft',
				'guid'                  => 'https://wpml.to.mlp/?p=4923',
				'date'                  =>  new DateTime,
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'type'                  => 'post',
				'excerpt'               => 'Mocky the fighter',
				'content'               => 'Mock will go for a great fight.',
				'name'                  => 'mocky-test-fight',
				'origin_parent_post_id' => 2,
				'menu_order'            => 0,
				'password'              => '',
				'is_sticky'             => TRUE,
				'origin_link'           => 'https://wpml.to.mlp/2016/mocky-test-fight',
			],
			# 2. parameter $meta_data
			[
				[
					'key'       => '_post_thumbnail',
					'value'     => '3',
					'is_single' => TRUE,
				],
				[
					'key'       => '_w2m_single_serialized_data',
					'value'     => [ 'foo' => 'bar', 'one' => 'two' ],
					'is_single' => TRUE,
				],
				[
					'key'       => '_w2m_multiple_scalar_data',
					'value'     => [ 'one', 'two', 'three' ],
					'is_single' => FALSE,
				]
			],
			# 3. parameter $id_map
			[
				'post_parent_id' => 3,
				'post_author_id' => 4
			]
		];

		return $data;
	}
}
