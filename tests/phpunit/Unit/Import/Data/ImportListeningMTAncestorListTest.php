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

		$origin_id = 948;
		$origin_parent_id = 903;

		$wp_term_mock = $this->mock_builder->wp_term();
		$import_term_mock = $this->mock_builder->type_wp_import_term();
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

		$wp_post_mock = $this->mock_builder->wp_term();
		$testee = new Data\ImportListeningMTAncestorList;

		foreach ( $terms as $term ) {
			$import_post_mock = $this->mock_builder->type_wp_import_term();
			$import_post_mock->method( 'origin_id' )
				->willReturn( $term[ 'origin_id' ] );
			$import_post_mock->method( 'origin_parent_term_id' )
				->willReturn( $term[ 'origin_parent_id' ] );
			$testee->record_term_ancestor( $wp_post_mock, $import_post_mock );
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

	public function test_no_side_effect_post_term() {

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
			$testee->relations( 'terms' )
		);
	}

	public function test_no_side_effect_term_post() {

		$origin_id = 12;
		$origin_parent_id = 34;

		$wp_term_mock = $this->mock_builder->wp_term();
		$import_term_mock = $this->mock_builder->type_wp_import_term();
		$import_term_mock->method( 'origin_id' )
			->willReturn( $origin_id );
		$import_term_mock->method( 'origin_parent_term_id' )
			->willReturn( $origin_parent_id );

		$testee = new Data\ImportListeningMTAncestorList;
		$testee->record_term_ancestor( $wp_term_mock, $import_term_mock );

		// terms should be empty
		$this->assertEmpty(
			$testee->relations( 'terms' )
		);
	}
}
