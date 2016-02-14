<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

/**
 * Interface ValueFilterableInterface
 *
 * During the process of (XML pull) import it is not stated that
 * referenced instances already exists. With this interface, implementors
 * have the ability to report this situation to get queued to the
 * end of the process.
 *
 * @package W2M\Import\Filter
 */
interface ValueFilterableInterface extends ValueFilterInterface {

	/**
	 * Return FALSE when the filter is not filterable due to missing data. E.g. a post
	 * ID refers to a post which was not imported yet.
	 *
	 * @param mixed $value
	 * @param int $object_id
	 *
	 * @return bool
	 */
	public function is_filterable( $value, $object_id );
}