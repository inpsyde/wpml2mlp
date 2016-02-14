<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Data;

/**
 * Class RecursiveIdMetaValueFilter
 *
 * Filter traversable (meta) value and treat given key(s) as object ID of a given type.
 * Looks up the ID in a given MultiTypeIdMapperInterface.
 *
 *
 * @package W2M\Import\Filter
 */
class RecursiveIdMetaValueFilter implements ValueFilterableInterface {

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_map;

	/**
	 * @var array
	 */
	private $key_type_map;

	/**
	 * @param array $key_type_map
	 * @param Data\MultiTypeIdMapperInterface $id_map
	 */
	public function __construct( Array $key_type_map, Data\MultiTypeIdMapperInterface $id_map ) {

		$this->key_type_map = $key_type_map;
		$this->id_map       = $id_map;
	}

	/**
	 * @param mixed $value
	 * @param int $object_id (Unused)
	 *
	 * @return mixed (Same type as $value)
	 */
	public function filter( $value, $object_id ) {

		if ( ! $this->is_filterable( $value, $object_id ) )
			return $value;

		if ( ! is_array( $value ) && ! is_object( $value ) )
			return $value;

		return $this->recursive_replacement( $value );
	}

	/**
	 * Return FALSE when the filter is not filterable due to missing data. E.g. a post
	 * ID refers to a post which was not imported yet.
	 *
	 * @param mixed $value
	 * @param int $object_id (Unused)
	 *
	 * @return bool
	 */
	public function is_filterable( $value, $object_id ) {

		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return TRUE; // Note, we don't want to get called twice
		}

		return $this->recursive_lookup( $value );
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function recursive_replacement( $value ) {

		foreach ( $value as $key => &$record ) {
			if ( ! is_scalar( $record ) ) {
				$record = $this->recursive_replacement( $record );
				continue;
			}
			if ( ! isset( $this->key_type_map[ $key ] ) )
				continue;
			$type = $this->key_type_map[ $key ];
			$record = $this->id_map->local_id( $type, (int) $record );
		}

		return $value;
	}


	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function recursive_lookup( $value ) {

		/**
		 * This is not really DRY as it copies the iteration logic from recursive_replacement.
		 * A simple comparison between the original and replaced value is not sufficient as it
		 * might be possible that original and mapped IDs are equivalent. The iteration should
		 * be refactored with recursive iterators maybe.
		 */
		$filterable = FALSE;
		foreach ( $value as $key => $record ) {
			if ( ! is_scalar( $record ) ) {
				$filterable = $this->recursive_lookup( $record );
				continue;
			}
			if ( ! isset( $this->key_type_map[ $key ] ) ) {
				continue;
			}
			$type = $this->key_type_map[ $key ];
			$filterable = (bool) $this->id_map->local_id( $type, (int) $record );
		}

		return $filterable;
	}
}