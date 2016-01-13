<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Module;

use
	W2M\Import\Module,
	W2M\Test\Helper,
	Brain;

class MlpTranslationConnectorTest extends Helper\MonkeyTestCase {

	public function test_get_blog_by_locale() {

		$mlp_content_relation_mock = $this->mock_builder->mlp_content_relations_interface();
		$data_mapper_mock          = $this->mock_builder->data_multi_type_id_mapper();
		$wp_factory_mock           = $this->mock_builder->common_wp_factory();

		Brain\Monkey::functions()
			->expect( 'mlp_get_available_languages' )
			->zeroOrMoreTimes()
			->with( TRUE )
			->andReturn( [ 1 => 'de_DE', 2 => 'fr_FR', 3 => 'en_US' ] );

		$testee = new Module\MlpTranslationConnector(
			$mlp_content_relation_mock,
			$data_mapper_mock,
			$wp_factory_mock
		);

		$this->assertSame(
			1,
			$testee->get_blog_id_by_locale( 'de_DE' )
		);
		$this->assertSame(
			2,
			$testee->get_blog_id_by_locale( 'fr_FR' )
		);
		$this->assertSame(
			3,
			$testee->get_blog_id_by_locale( 'en_US' )
		);

		$this->assertSame(
			1,
			$testee->get_blog_id_by_locale( 'de' )
		);
		$this->assertSame(
			2,
			$testee->get_blog_id_by_locale( 'fr' )
		);
		$this->assertSame(
			3,
			$testee->get_blog_id_by_locale( 'en' )
		);

	}

	public function test_get_remote_post_id() {

		$blog_id   = 159;
		$origin_id = 14;
		$post_id   = 3;

		$mlp_content_relation_mock = $this->mock_builder->mlp_content_relations_interface();
		$data_mapper_mock          = $this->mock_builder->data_multi_type_id_mapper();
		$wp_factory_mock           = $this->mock_builder->common_wp_factory();
		$wp_query_mock             = $this->mock_builder->wp_query();
		$wp_query_mock->posts = [ $post_id ];

		$wp_factory_mock->expects( $this->once() )
			->method( 'wp_query' )
			->with(
				[
					'posts_per_page'         => 1,
					'post_status'            => 'any',
					'post_type'              => 'any',
					'meta_key'               => '_w2m_origin_post_id',
					'meta_value'             => $origin_id,
					'fields'                 => 'ids',
					'update_post_meta_cache' => FALSE,
					'update_post_term_cache' => FALSE
				]
			)
			->willReturn( $wp_query_mock );

		$locale_relation_mock = $this->mock_builder->type_locale_relation();
		$locale_relation_mock->method( 'origin_id' )
			->willReturn( $origin_id );

		Brain\Monkey::functions()
			->expect( 'switch_to_blog' )
			->once()
			->with( $blog_id );

		Brain\Monkey::functions()
			->expect( 'restore_current_blog' )
			->once();

		$testee = new Module\MlpTranslationConnector(
			$mlp_content_relation_mock,
			$data_mapper_mock,
			$wp_factory_mock
		);

		$this->assertSame(
			$post_id,
			$testee->get_remote_post_id( $blog_id, $locale_relation_mock )
		);

	}

	public function test_link_posts() {

		$current_blog_id = 1;
		$post_id         = 2;
		$blog_locales = [
			// blog_id => locale
			1 => 'de_DE',
			2 => 'fr_FR',
			3 => 'en_US',
			4 => 'it_IT'
		];
		$relation_data = [
			// location => origin_id of the post
			'it_IT' => 4,
			'en_US' => 7,
			'fr_FR' => 11
		];
		$locale_relation_mocks = [];
		foreach ( $relation_data as $locale => $origin_id ) {
			$relation_mock = $this->mock_builder->type_locale_relation();
			$relation_mock->method( 'locale' )->willReturn( $locale );
			$relation_mock->method( 'origin_id' )->willReturn( $origin_id );
			$locale_relation_mocks[] = $relation_mock;
		}
		$mlp_content_relation_mock = $this->mock_builder->mlp_content_relations_interface( [ 'set_relation' ] );
		$data_mapper_mock          = $this->mock_builder->data_multi_type_id_mapper();
		$wp_factory_mock           = $this->mock_builder->common_wp_factory();
		$import_post_mock          = $this->mock_builder->type_wp_import_post();

		$import_post_mock->method( 'locale_relations' )
			->willReturn( $locale_relation_mocks );
		$import_post_mock->method( 'id' )
			->willReturn( $post_id );

		$remote_post_mocks = [
			$this->mock_builder->wp_post(),
			$this->mock_builder->wp_post(),
			$this->mock_builder->wp_post()
		];
		$remote_post_mocks[ 0 ]->posts = [ 20 ];
		$remote_post_mocks[ 1 ]->posts = [ 30 ];
		$remote_post_mocks[ 2 ]->posts = [ 40 ];

		$wp_factory_mock->expects( $this->exactly( 3 ) )
			->method( 'wp_query' )
			->withConsecutive(
				[ $this->equalTo( [
					'posts_per_page'         => 1,
					'post_status'            => 'any',
					'post_type'              => 'any',
					'meta_key'               => '_w2m_origin_post_id',
					'meta_value'             => $relation_data[ 'it_IT' ],
					'fields'                 => 'ids',
					'update_post_meta_cache' => FALSE,
					'update_post_term_cache' => FALSE
				] ) ],
				[ $this->equalTo( [
					'posts_per_page'         => 1,
					'post_status'            => 'any',
					'post_type'              => 'any',
					'meta_key'               => '_w2m_origin_post_id',
					'meta_value'             => $relation_data[ 'en_US' ],
					'fields'                 => 'ids',
					'update_post_meta_cache' => FALSE,
					'update_post_term_cache' => FALSE
				] ) ],
				[ $this->equalTo( [
					'posts_per_page'         => 1,
					'post_status'            => 'any',
					'post_type'              => 'any',
					'meta_key'               => '_w2m_origin_post_id',
					'meta_value'             => $relation_data[ 'fr_FR' ],
					'fields'                 => 'ids',
					'update_post_meta_cache' => FALSE,
					'update_post_term_cache' => FALSE
				] ) ]
			)
			->will(
				$this->onConsecutiveCalls(
					$remote_post_mocks[ 0 ],
					$remote_post_mocks[ 1 ],
					$remote_post_mocks[ 2 ]
				)
			);

		$mlp_content_relation_mock->expects( $this->exactly( 3 ) )
			->method( 'set_relation' )
			->withConsecutive(
				[ $current_blog_id, 4, $post_id, 20 ],
				[ $current_blog_id, 3, $post_id, 30 ],
				[ $current_blog_id, 2, $post_id, 40 ]
			);

		Brain\Monkey::functions()
			->expect( 'update_post_meta' )
			->once();

		Brain\Monkey::functions()
			->expect( 'mlp_get_available_languages' )
			->zeroOrMoreTimes()
			->with( TRUE )
			->andReturn( $blog_locales );

		Brain\Monkey::functions()
			->expect( 'get_current_blog_id' )
			->andReturn( $current_blog_id );

		$testee = new Module\MlpTranslationConnector(
			$mlp_content_relation_mock,
			$data_mapper_mock,
			$wp_factory_mock
		);

		$testee->link_post( $this->mock_builder->wp_post(), $import_post_mock );
	}

	public function test_link_terms() {

		$this->markTestSkipped( 'Under construction …' );
	}
}
