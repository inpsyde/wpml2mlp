<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Data;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Test\Helper;

class ImportListeningMTAncestorListTest extends Helper\MonkeyTestCase {

	public function test_record_post_ancestor() {

		$origin_id = 4983;
		$origin_parent_id = 5839;

		$wp_post_mock = $this->mock_builder->wp_post();
		$import_post_mock = $this->mock_builder->type_wp_import_post();
		$import_post_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_post_mock->method( 'origin_parent_post_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_post_ancestor( $wp_post_mock, $import_post_mock );
		$result = $testee->relations( 'post' );

		$this->assertCount(
			1,
			$result
		);

		$this->assertInstanceOf(
			'W2M\Import\Type\AncestorRelationInterface',
			current( $result )
		);
		/* @type Type\AncestorRelationInterface $relation */
		$relation = current( $result );
		$this->assertSame(
			$origin_parent_id,
			$relation->parent_id()
		);
		$this->assertSame(
			$origin_id,
			$relation->id()
		);
	}

	public function test_record_term_ancestor() {

		$origin_id        = 948;
		$origin_parent_id = 903;
		$taxonomy         = 'category';
		$wp_term_mock     = $this->mock_builder->wp_term();
		$import_term_mock = $this->mock_builder->type_wp_import_term();

		$wp_term_mock->taxonomy = $taxonomy;
		$import_term_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_term_mock->method( 'origin_parent_term_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_term_ancestor( $wp_term_mock, $import_term_mock );

		$result = $testee->relations( 'term' );

		$this->assertCount(
			1,
			$result
		);

		$this->assertInstanceOf(
			'W2M\Import\Type\AncestorRelationInterface',
			current( $result )
		);
		/* @type Type\AncestorRelationInterface $relation */
		$relation = current( $result );
		$this->assertSame(
			$origin_parent_id,
			$relation->parent_id()
		);
		$this->assertSame(
			$origin_id,
			$relation->id()
		);
	}

	public function test_record_comment_ancestor() {

		$origin_id           = 1;
		$origin_parent_id    = 1;
		$wp_comment_mock     = $this->getMockBuilder( 'WP_Comment' )
			->getMock();
		$import_comment_mock = $this->mock_builder->type_wp_import_comment();

		$import_comment_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_comment_mock->method( 'origin_parent_comment_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_comment_ancestor( $wp_comment_mock, $import_comment_mock );
		$result = $testee->relations( 'comment' );

		$this->assertCount(
			1,
			$result
		);

		/* @var Type\AncestorRelationInterface $relation */
		$relation = current( $result );
		$this->assertInstanceOf(
			'W2M\Import\Type\AncestorRelationInterface',
			$relation
		);
		$this->assertSame(
			$origin_id,
			$relation->id()
		);
		$this->assertSame(
			$origin_parent_id,
			$relation->parent_id()
		);
	}

	public function test_record_post_no_duplicates() {

		$posts = array(
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			),
			array(
				'origin_id' => 123,
				'origin_parent_id' => 4567
			),
			array(
				'origin_id' => 567,
				'origin_parent_id' => 1234
			),
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 532
			),
			// duplicate of the first
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			)
		);

		$wp_post_mock = $this->mock_builder->wp_post();
		$testee = new Data\ImportListeningMTAncestorList;

		foreach ( $posts as $post ) {
			$import_post_mock = $this->mock_builder->type_wp_import_post();
			$import_post_mock->method( 'origin_id' )
				->willReturn( $post[ 'origin_id' ] );
			$import_post_mock->method( 'origin_parent_post_id' )
				->willReturn( $post[ 'origin_parent_id' ] );
			$testee->record_post_ancestor( $wp_post_mock, $import_post_mock );
		}

		array_pop( $posts );
		$relations = $testee->relations( 'post' );
		$this->assertCount(
			count( $posts ),
			$relations
		);
		foreach ( $posts as $index => $post ) {
			$this->assertSame(
				$post[ 'origin_id' ],
				$relations[ $index ]->id(),
				"Test failed for index {$index}"
			);
			$this->assertSame(
				$post[ 'origin_parent_id' ],
				$relations[ $index ]->parent_id(),
				"Test failed for index {$index}"
			);
		}
	}

	public function test_record_term_no_duplicates() {

		$terms = array(
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			),
			array(
				'origin_id' => 123,
				'origin_parent_id' => 4567
			),
			array(
				'origin_id' => 567,
				'origin_parent_id' => 1234
			),
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 532
			),
			// duplicate of the first
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			)
		);
		$taxonomy = 'category';

		$wp_term_mock = $this->mock_builder->wp_term();
		$wp_term_mock->taxonomy = $taxonomy;
		$testee = new Data\ImportListeningMTAncestorList;

		foreach ( $terms as $term ) {
			$import_term_mock = $this->mock_builder->type_wp_import_term();
			$import_term_mock->method( 'origin_id' )
				->willReturn( $term[ 'origin_id' ] );
			$import_term_mock->method( 'origin_parent_term_id' )
				->willReturn( $term[ 'origin_parent_id' ] );
			$testee->record_term_ancestor( $wp_term_mock, $import_term_mock );
		}

		array_pop( $terms );
		$relations = $testee->relations( 'term' );
		$this->assertCount(
			count( $terms ),
			$relations
		);
		foreach ( $terms as $index => $term ) {
			$this->assertSame(
				$term[ 'origin_id' ],
				$relations[ $index ]->id(),
				"Test failed for index {$index}"
			);
			$this->assertSame(
				$term[ 'origin_parent_id' ],
				$relations[ $index ]->parent_id(),
				"Test failed for index {$index}"
			);
		}
	}

	public function test_record_comment_no_duplicated() {

		$comments = array(
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			),
			array(
				'origin_id' => 123,
				'origin_parent_id' => 4567
			),
			array(
				'origin_id' => 567,
				'origin_parent_id' => 1234
			),
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 532
			),
			// duplicate of the first
			array(
				'origin_id' => 1234,
				'origin_parent_id' => 567
			)
		);

		$wp_comment_mock = $this->mock_builder->wp_comment();
		$testee = new Data\ImportListeningMTAncestorList;

		foreach ( $comments as $term ) {
			$import_comment_mock = $this->mock_builder->type_wp_import_comment();
			$import_comment_mock->method( 'origin_id' )
				->willReturn( $term[ 'origin_id' ] );
			$import_comment_mock->method( 'origin_parent_comment_id' )
				->willReturn( $term[ 'origin_parent_id' ] );
			$testee->record_comment_ancestor( $wp_comment_mock, $import_comment_mock );
		}

		array_pop( $comments );
		$relations = $testee->relations( 'comment' );
		$this->assertCount(
			count( $comments ),
			$relations
		);
		foreach ( $comments as $index => $term ) {
			$this->assertSame(
				$term[ 'origin_id' ],
				$relations[ $index ]->id(),
				"Test failed for index {$index}"
			);
			$this->assertSame(
				$term[ 'origin_parent_id' ],
				$relations[ $index ]->parent_id(),
				"Test failed for index {$index}"
			);
		}
	}

	public function test_no_side_effect_with_post() {

		$origin_id = 4983;
		$origin_parent_id = 5839;

		$wp_post_mock = $this->mock_builder->wp_post();
		$import_post_mock = $this->mock_builder->type_wp_import_post();
		$import_post_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_post_mock->method( 'origin_parent_post_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_post_ancestor( $wp_post_mock, $import_post_mock );

		// terms should be empty
		$this->assertEmpty(
			$testee->relations( 'term' )
		);
		// comments should be empty
		$this->assertEmpty(
			$testee->relations( 'comment' )
		);
	}

	public function test_no_side_effect_with_term() {

		$origin_id        = 12;
		$origin_parent_id = 34;
		$taxonomy         = 'post_tag';
		$wp_term_mock     = $this->mock_builder->wp_term();
		$import_term_mock = $this->mock_builder->type_wp_import_term();

		$wp_term_mock->taxonomy = $taxonomy;
		$import_term_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_term_mock->method( 'origin_parent_term_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_term_ancestor( $wp_term_mock, $import_term_mock );

		// post should be empty
		$this->assertEmpty(
			$testee->relations( 'post' )
		);
		// comment should be empty
		$this->assertEmpty(
			$testee->relations( 'comment' )
		);
	}

	public function test_no_side_effect_with_comment() {

		$origin_id = 12;
		$origin_parent_id = 34;

		$wp_comment_mock = $this->mock_builder->wp_comment();
		$import_comment_mock = $this->mock_builder->type_wp_import_comment();
		$import_comment_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_comment_mock->method( 'origin_parent_term_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_comment_ancestor( $wp_comment_mock, $import_comment_mock );

		// post should be empty
		$this->assertEmpty(
			$testee->relations( 'post' )
		);
		// term should be empty
		$this->assertEmpty(
			$testee->relations( 'term' )
		);
	}
}
