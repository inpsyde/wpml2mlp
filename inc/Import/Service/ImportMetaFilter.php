<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Filter,
	W2M\Import\Type;

/**
 * Class ImportMetaFilter
 *
 * Single instance (composite) that applies filters from a list to any meta value
 * of a specific object type.
 *
 * @package W2M\Import\Filter
 */
class ImportMetaFilter implements ImportMetaFilterInterface {

	/**
	 * @var Data\MetaFilterListInterface
	 */
	private $filter_list;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param Data\MetaFilterListInterface $filter_list
	 * @param string $type
	 */
	public function __construct(
		Data\MetaFilterListInterface $filter_list,
		$type
	) {

		$this->filter_list = $filter_list;
		$this->type        = (string) $type;
	}

	/**
	 * @param Type\ImportMetaInterface $meta
	 * @param int $object_id
	 *
	 * @return mixed
	 */
	public function filter_value( Type\ImportMetaInterface $meta, $object_id ) {

		if ( $meta->is_single() )
			return $this->apply_filters( $meta, $meta->value(), $object_id );

		$value = [];
		foreach ( $meta->value() as $index => $v ) {
			$value[] = $this->apply_filters( $meta, $v, $object_id, $index );
		}

		return $value;
	}

	/**
	 * @param Type\ImportMetaInterface $meta
	 * @param string $value
	 * @param int $object_id
	 * @param int $index (Index of the meta record for multiple meta data)
	 *
	 * @return mixed
	 */
	public function apply_filters( Type\ImportMetaInterface $meta, $value, $object_id, $index = 0 ) {

		$filters = $this->filter_list->get_filters( $this->type, $meta->key() );
		/**
		 * @todo #56: Return unfiltered value if one filter is not filterable. But still iterate over every
		 * available filter.
		 */
		foreach ( $filters as $filter ) {
			if ( ! $filter->is_filterable( $meta->value(), $object_id ) ) {
				$meta_index = new Type\WpMetaRecordIndex( $meta->key(), $object_id, $index, $this->type );
				/**
				 * Fires when a filter is invalid (due to missing data)
				 *
				 * @param Filter\ValueFilterableInterface $filter
				 * @param Type\MetaRecordIndexInterface $meta_index
				 * @param Type\ImportMetaInterface $meta
				 */
				do_action( 'w2m_import_meta_not_filterable', $filter, $meta_index, $meta );
				continue;
			}
			$value = $filter->filter( $value, $object_id );
		}

		return $value;
	}

}