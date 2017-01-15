<?php # -*- coding: utf-8 -*-

namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	W2M\Import\Type,
	W2M\Test\Helper,
	WP_Post,
	DateTime;

/**
 * Class WpPostImporterTest
 *
 * System test for the complete post import module. Verifies that every part
 * (post importing, meta importing, term importing) works as expected.
 *
 * This does not replace unit tests for each of the single modules.
 *
 * However it's created in preparation of the refactoring of the God-Object WpPostImporter
 * to gain more SOC. Todo: #57
 * During the refactoring we just need do configure the new modules (meta importer, term linker),
 * and all the tests should still pass as the majority are black box tests.
 *
 * @package W2M\Test\WpIntegration\Import\Service
 */
class WpPostImporterTest extends Helper\WpIntegrationTestCase {

	/**
	 * @group import
	 * @dataProvider import_post_test_data
	 *
	 * @param array $post_data
	 * @param array $meta_data
	 * @param array $id_map
	 */
	public function test_import_post( Array $post_data, Array $meta_data, Array $id_map ) {

		$http_mock = $this->mock_builder->wp_http();
		$http_mock
			->expects( $this->never() )
			->method( 'request' );

		$term_data        = $this->create_terms();
		$import_post_mock = $this->build_import_post_mock( $post_data, $meta_data, $term_data );
		$id_mapper_mock   = $this->build_id_map_mock( $post_data, $id_map, $term_data );

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

		/**
		 * Todo: #57
		 * Instantiate the new actors here, and bind them to the action.
		 * The tests should still pass then.
		 */
		add_action(
			$text_action,
			/**
			 * @param WP_Post $wp_post
			 * @param Type\ImportPostInterface $import_post
			 */
			function( $wp_post, $import_post )
				use ( $action_check, $import_post_mock, $meta_data, $id_map, $term_data )
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
				$this->assertSame(
					$wp_post->ID,
					$import_post->id()
				);
				$this->make_post_assertions( $wp_post, $import_post, $id_map );
				$this->make_meta_assertions( $wp_post, $meta_data );
				$this->make_term_assertions( $wp_post, $import_post, $term_data );
				$this->make_post_stick_assertion( $wp_post, $import_post );
			},
			10,
			2
		);

		$testee = new Service\Importer\WpPostImporter( $id_mapper_mock, $http_mock );
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
			$id_map[ 'parent_post_id' ],
			(int) $wp_post->post_parent
		);
		$this->assertSame(
			$id_map[ 'author_id' ],
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
	 * Test if the post is actual sticky if it should be
	 *
	 * @param WP_Post $post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function make_post_stick_assertion( WP_Post $post, Type\ImportPostInterface $import_post ) {

		$sticky_posts = get_option( 'sticky_posts', [] );
		if ( $import_post->is_sticky() ) {
			$this->assertContains(
				$post->ID,
				$sticky_posts
			);
		} else {
			$this->assertNotContains(
				$post->ID,
				$sticky_posts
			);
		}
	}

	/**
	 * compare post terms with import data
	 *
	 * @param WP_Post $post
	 * @param Type\ImportPostInterface $import_post
	 * @param array $term_data
	 */
	public function make_term_assertions(
		WP_Post $post,
		Type\ImportPostInterface $import_post,
		Array $term_data
	) {

		/**
		 * @param array $list
		 * @param $key
		 *
		 * @return array
		 */
		$list_pluck = function( Array $list, $key ) {
			$sub_list = [];
			foreach ( $list as $v ) {
				$sub_list[] = $v[ $key ];
			}

			return $sub_list;
		};
		$taxonomies        = $list_pluck( $term_data, 'taxonomy' );
		$expected_term_ids = $list_pluck( $term_data, 'term_id' );
		$post_term_ids     = wp_get_post_terms( $post->ID, $taxonomies, [ 'fields' => 'ids' ] );

		$this->assertSame(
			$expected_term_ids,
			$post_term_ids
		);

		$expected_term_names = $list_pluck( $term_data, 'name' );
		$post_term_names     = wp_get_post_terms( $post->ID, $taxonomies, [ 'fields' => 'names' ] );
		$this->assertSame(
			$expected_term_names,
			$post_term_names
		);
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
		foreach ( $term_data as $term ) {
			$post_data[ 'terms' ][] = $this->mock_builder->type_wp_term_reference(
				[],
				[
					'origin_id' => $term[ 'origin_id' ],
					'taxonomy'  => $term[ 'taxonomy' ]
				]
			);
		}

		$post_mock = $this->mock_builder->type_wp_import_post( [], $post_data );

		/**
		 * WpPostImporter MUST pass the new wp-post id to the import post object.
		 * Its used as a one-time setter. Each ensuing call will return this new post id.
		 * This is asserted in test_import_post()
		 */
		$post_mock->expects( $this->atLeast( 1 ) )
			->method( 'id' )
			->willReturnCallback(
				function( $id = NULL ) {
					static $local_id;
					if ( ! $local_id ) {
						$local_id = 0;
					}
					if ( ! $id || $local_id )
						return $local_id;

					$local_id = (int) $id;
					return $local_id;
				}
			);

		return $post_mock;
	}

	/**
	 * Create and insert test terms and return a $data array about this terms
	 *
	 * @return array
	 */
	public function create_terms() {

		$category_data = [
			'name'      => 'First category',
			'taxonomy'  => 'category',
			'origin_id' => 42
		];
		$tag_data = [
			'name'      => 'Second post tag',
			'taxonomy'  => 'post_tag',
			'origin_id' => 24
		];

		$category = wp_insert_term(
			$category_data[ 'name' ],
			$category_data[ 'taxonomy' ]
		);
		$post_tag = wp_insert_term(
			$tag_data[ 'name' ],
			$tag_data[ 'taxonomy' ]
		);

		$terms = [
			array_merge( $category_data, $category ),
			array_merge( $tag_data, $post_tag )
		];

		return $terms;
	}

	/**
	 * @param array $post_data
	 * @param array $id_map
	 * @param array $terms
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function build_id_map_mock( Array $post_data, Array $id_map, Array $terms ) {

		$id_mapper_mock = $this->mock_builder->data_multi_type_id_mapper();
		$id_mapper_mock
			->expects( $this->atLeast( 1 ) )
			->method( 'local_id' )
			->willReturnCallback(
				function( $type, $origin_id ) use ( $post_data, $id_map, $terms ) {

					$post_map = [
						$post_data[ 'origin_parent_post_id' ] => $id_map[ 'parent_post_id' ]
					];
					$user_map = [
						$post_data[ 'origin_author_id' ] => $id_map[ 'author_id' ]
					];
					$term_map = [];
					foreach ( $terms as $term ) {
						$term_map[ $term[ 'origin_id' ] ] = $term[ 'term_id' ];
					}

					$local_id = NULL;
					switch ( $type ) {
						case 'term' :
							if ( isset( $term_map[ $origin_id ] ) ) {
								$local_id = $term_map[ $origin_id ];
							}
							break;

						case 'post' :
							if ( isset( $post_map[ $origin_id ] ) ) {
								$local_id = $post_map[ $origin_id ];
							}
							break;

						case 'user' :
							if ( isset( $user_map[ $origin_id ] ) ) {
								$local_id = $user_map[ $origin_id ];
							}
							break;
					}

					return $local_id;
				}
			);

			/*
			->withConsecutive(
				array( 'post', $post_data[ 'origin_parent_post_id' ] ),
				array( 'user', $post_data[ 'origin_author_id' ] )
			)
			->will(
				$this->onConsecutiveCalls(
					$id_map[ 'parent_post_id'],
					$id_map[ 'author_id' ]
				)
			);
			*/

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
				'parent_post_id' => 3,
				'author_id' => 4
			]
		];

		return $data;
	}
}
