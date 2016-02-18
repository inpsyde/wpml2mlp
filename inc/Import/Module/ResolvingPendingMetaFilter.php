<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data,
	W2M\Import\Filter,
	W2M\Import\Type;

/**
 * Class ResolvingPendingMetaFilter
 *
 * Reads postponed meta filter from the list and
 * tries to re-apply them at the end of the import-process.
 *
 * @package W2M\Import\Module
 */
class ResolvingPendingMetaFilter {

	/**
	 * @var Data\ImportListeningMetaFilterList
	 */
	private $meta_list;

	/**
	 * @param Data\ImportListeningMetaFilterList $meta_filter_list
	 */
	public function __construct( Data\ImportListeningMetaFilterList $meta_filter_list ) {

		$this->meta_list = $meta_filter_list;
	}

	/**
	 * @wp-hook w2m_import_process_done
	 */
	public function resolve_pending_meta_filter() {

		/**
		 * Used to attach process logger
		 *
		 * @param Data\ImportListeningMetaFilterList $meta_list
		 */
		do_action( 'w2m_import_meta_filter_resolving_start', $this->meta_list );

		$post_filter_storage = $this->meta_list->get_filters( 'post' );
		/**
		 * @var  array $filter_list
		 * @var Type\MetaRecordIndexInterface $meta_index
		 */
		foreach ( $post_filter_storage as $filter_list => $meta_index ) {
			/* @var array $data */
			foreach ( $filter_list as $data )
				$this->apply_post_filter( $meta_index, $data[ 'filter' ], $data[ 'meta' ] );
		}

		// Todo #56: Handle meta data for user, comment and term types
	}

	/**
	 * Todo: Exclude this to a separate service object
	 *
	 * @param Type\MetaRecordIndexInterface $meta_index
	 * @param Filter\ValueFilterableInterface $filter
	 * @param Type\ImportMetaInterface $meta
	 */
	private function apply_post_filter(
		Type\MetaRecordIndexInterface $meta_index,
		Filter\ValueFilterableInterface $filter,
		Type\ImportMetaInterface $meta
	) {

		$filtered_value = $this->get_filtered_value( $meta_index, $filter, $meta );
		$preview_value  = '';
		if ( ! $meta->is_single() ) {
			$meta_records = $meta->value();
			$preview_value = $meta_records[ $meta_index->index() ];
		}

		update_post_meta(
			$meta_index->object_id(),
			$meta_index->key(),
			$filtered_value,
			$preview_value
		);
	}

	/**
	 * Todo: Exclude this to a separate service object
	 *
	 * @param Type\MetaRecordIndexInterface $meta_index
	 * @param Filter\ValueFilterableInterface $filter
	 * @param Type\ImportMetaInterface $meta
	 *
	 * @return mixed
	 */
	private function get_filtered_value(
		Type\MetaRecordIndexInterface $meta_index,
		Filter\ValueFilterableInterface $filter,
		Type\ImportMetaInterface $meta
	) {

		if ( ! $filter->is_filterable( $meta->value(), $meta_index->object_id() ) ) {
			/**
			 * Fires when a filter is not resolvable
			 *
			 * @param Filter\ValueFilterableInterface $filter
			 * @param Type\MetaRecordIndexInterface $meta_index
			 * @param Type\ImportMetaInterface $meta
			 */
			do_action( 'w2m_import_meta_filter_not_resolvable', $filter, $meta_index, $meta );
		}

		if ( $meta->is_single() ) {
			return $filter->filter( $meta->value(), $meta_index->object_id() );
		}

		$meta_records = $meta->value();

		return $filter->filter(
			$meta_records[ $meta_index->index() ],
			$meta_index->object_id()
		);
	}
}