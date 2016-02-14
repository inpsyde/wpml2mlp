<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Data;

/**
 * Class SingleIdMetaValueFilter
 *
 * Filter scalar (meta) value (e.g. _thumbnail) and treat the value as object ID of a given type.
 * Looks up the ID in a given MultiTypeIdMapperInterface.
 *
 * @package W2M\Import\Filter
 */
class SingleIdMetaValueFilter implements ValueFilterableInterface {

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_map;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param $type
	 * @param Data\MultiTypeIdMapperInterface $id_map
	 */
	public function __construct( $type, Data\MultiTypeIdMapperInterface $id_map ) {

		$this->type   = (string) $type;
		$this->id_map = $id_map;
	}

	/**
	 * @param mixed $value
	 * @param int $object_id
	 *
	 * @return mixed (Same type as $value)
	 */
	public function filter( $value, $object_id ) {

		if ( ! $this->is_filterable( (int) $value, $object_id ) ) {
			return $value;
		}

		return $this->id_map->local_id( $this->type, (int) $value );
	}

	/**
	 * Return FALSE when the filter is not filterable due to missing data. E.g. a post
	 * ID refers to a post which was not imported yet.
	 *
	 * @param mixed $value
	 * @param int $object_id
	 *
	 * @return bool
	 */
	public function is_filterable( $value, $object_id ) {

		return (bool) $this->id_map->local_id( $this->type, (int) $value );
	}

}