<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

/**
 * Interface ValueFilterInterface
 *
 * Describes a unified filter for (meta) data
 *
 * @package W2M\Import\Filter
 */
interface ValueFilterInterface {

	/**
	 * The implementor MUST return the unfiltered value
	 * if it is not able to filter the value.
	 *
	 * @param mixed $value
	 * @param int $object_id
	 *
	 * @return mixed (Same type as $value)
	 */
	public function filter( $value, $object_id );
}