<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Data;

use
	W2M\Import\Data,
	W2M\Import\Filter,
	W2M\Import\Type,
	W2M\Test\Helper;

class ImportListeningMetaFilterListTest extends Helper\MonkeyTestCase {

	public function test_record_filter() {

		$type       = 'post';
		$filter     = $this->mock_builder->filter_value_filterable_interface();
		$meta_index = $this->mock_builder->type_meta_record_index_interface(
			[],
			[
				'key'       => '_thumbnail_id',
				'type'      => $type,
				'object_id' => 42,
				'index'     => 0
			]
		);
		$meta       = $this->mock_builder->type_wp_import_meta();

		$testee = new Data\ImportListeningMetaFilterList;
		$testee->record_meta_filter( $filter, $meta_index, $meta );
		$storage = $testee->get_filters( 'post' );

		$this->assertTrue(
			$storage->contains( $meta_index )
		);
		$this->assertSame(
			[
				'filter' => $filter,
				'meta'   => $meta
			],
			current( $storage->offsetGet( $meta_index ) )
		);
	}

	public function test_record_multiple_filters_per_meta_index() {

		$type        = 'term';
		$meta_index  = $this->mock_builder->type_meta_record_index_interface(
			[],
			[
				'key'       => 'some_term_meta',
				'type'      => $type,
				'object_id' => 145,
				'index'     => 0
			]
		);
		$filter_mocks = [
			$this->mock_builder->filter_value_filterable_interface(),
			$this->mock_builder->filter_value_filterable_interface(),
			$this->mock_builder->filter_value_filterable_interface()
		];
		$meta         = $this->mock_builder->type_wp_import_meta();

		$testee = new Data\ImportListeningMetaFilterList;
		foreach ( $filter_mocks as $filter ) {
			$testee->record_meta_filter( $filter, $meta_index, $meta );
		}

		$storage = $testee->get_filters( $type );

		$this->assertTrue(
			$storage->contains( $meta_index )
		);

		/**
		 * @var array $filter_meta_list[] {
		 *      @var Filter\ValueFilterableInterface $filter
		 *      @var Type\ImportMetaInterface $meta
		 * }
		 */
		$filter_meta_list = $storage->offsetGet( $meta_index );
		$this->assertCount(
			count( $filter_mocks ),
			$filter_meta_list
		);

		$filter_list = [];
		foreach ( $filter_meta_list as $filter_meta_pair ) {
			$filter_list[] = $filter_meta_pair[ 'filter' ];
			$this->assertSame(
				$meta,
				$filter_meta_pair[ 'meta' ]
			);
		}

		$this->assertSame(
			array_values( $filter_mocks ),
			array_values( $filter_list )
		);
	}

	public function test_record_filter_with_multiple_types() {

		// Todo: write test
		$this->markTestIncomplete( 'Under construction …');
	}
}
