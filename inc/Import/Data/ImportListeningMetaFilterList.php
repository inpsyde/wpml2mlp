<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Filter,
	W2M\Import\Type,
	SplObjectStorage;

/**
 * Class ImportListeningMetaFilterList
 *
 * Listens to w2m_import_meta_not_filterable and collect
 * all postponed meta filters.
 *
 * @todo: Specify an interface for this
 *
 * @package W2M\Import\Data
 */
class ImportListeningMetaFilterList {

	/**
	 * A SplObjectStorage per type
	 *
	 * @var SplObjectStorage[]
	 */
	private $filters = [];

	/**
	 * Sets up internal structures
	 */
	public function __construct() {

		$this->filters = [
			'comment' => new SplObjectStorage,
			'post'    => new SplObjectStorage,
			'term'    => new SplObjectStorage,
			'user'    => new SplObjectStorage
		];
	}

	/**
	 * @wp-hook w2m_import_meta_not_filterable
	 *
	 * @param Filter\ValueFilterableInterface $filter
	 * @param Type\MetaRecordIndexInterface $meta_index
	 * @param Type\ImportMetaInterface $meta
	 */
	public function record_meta_filter(
		Filter\ValueFilterableInterface $filter,
		Type\MetaRecordIndexInterface $meta_index,
		Type\ImportMetaInterface $meta
	) {

		$this->register_type( $meta_index->type() );
		if ( $this->filter_exists( $filter, $meta_index ) )
			return;

		$this->push_filter( $filter, $meta_index, $meta );
	}

	/**
	 * @param string $type
	 *
	 * @return SplObjectStorage
	 */
	public function get_filters( $type ) {

		return isset( $this->filters[ $type ] )
			? $this->filters[ $type ]
			: new SplObjectStorage;
	}

	/**
	 * @param $type
	 */
	private function register_type( $type ) {

		if ( isset( $this->filters[ $type ] ) )
			return;

		$this->filters[ $type ] = new SplObjectStorage;
	}

	/**
	 * @param Filter\ValueFilterableInterface $filter
	 * @param Type\MetaRecordIndexInterface $meta_index
	 *
	 * @return bool
	 */
	private function filter_exists(
		Filter\ValueFilterableInterface $filter,
		Type\MetaRecordIndexInterface $meta_index
	) {

		$storage   = $this->filters[ $meta_index->type() ];
		$filter_id = spl_object_hash( $filter );

		if ( ! $storage->contains( $meta_index ) )
			return FALSE;

		$filters = $storage->offsetGet( $meta_index );

		return isset( $filters[ $filter_id ] );
	}

	/**
	 * @param Filter\ValueFilterableInterface $filter
	 * @param Type\MetaRecordIndexInterface $meta_index
	 * @param Type\ImportMetaInterface $meta
	 */
	private function push_filter(
		Filter\ValueFilterableInterface $filter,
		Type\MetaRecordIndexInterface $meta_index,
		Type\ImportMetaInterface $meta
	) {

		$storage      = $this->filters[ $meta_index->type() ];
		$filter_id    = spl_object_hash( $filter );
		$meta_filters = $storage->contains( $meta_index )
			? $storage->offsetGet( $meta_index )
			: [];
		$meta_filters[ $filter_id ] = [
			'filter' => $filter,
			'meta'   => $meta
		];

		$storage->attach( $meta_index, $meta_filters );
	}
}