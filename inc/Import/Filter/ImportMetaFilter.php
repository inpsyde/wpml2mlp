<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Data,
	W2M\Import\Filter,
	W2M\Import\Type;

/**
 * Class ImportMetaFilter
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

		$filters = $this->filter_list->get_filters( $this->type, $meta->key() );
		$value   = $meta->value();
		foreach ( $filters as $filter ) {
			if ( ! $filter->is_filterable( $meta->value(), $object_id ) ) {
				/**
				 * Fires when a filter is invalid (due to missing data)
				 *
				 * @param string $type ('post', 'comment', 'user' or 'term')
				 * @param int $object_id
				 * @param Type\ImportMetaInterface $meta
				 * @param Filter\ValueFilterableInterface $filter
				 */
				do_action( 'w2m_import_meta_not_filterable', $this->type, $object_id, $meta, $filter );
				continue;
			}
			$value = $filter->filter( $value, $object_id );
		}

		return $value;
	}

}