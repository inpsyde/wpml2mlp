<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Filter;

/**
 * Interface MetaFilterListInterface
 *
 * Manages a list of Filter\ValueFilterableInterface for each type:meta-key pair
 *
 * @package W2M\Import\Data
 */
interface MetaFilterListInterface {

	/**
	 * @param string $type
	 * @param string $key
	 * @param Filter\ValueFilterableInterface $filter
	 *
	 * @return bool
	 */
	public function push_filter( $type, $key, Filter\ValueFilterableInterface $filter );

	/**
	 * @param string $type
	 * @param string $key
	 * @param Filter\ValueFilterableInterface $filter
	 *
	 * @return bool
	 */
	public function pop_filter( $type, $key, Filter\ValueFilterableInterface $filter );

	/**
	 * @param string $type
	 * @param string $key
	 *
	 * @return Filter\ValueFilterableInterface[]
	 */
	public function get_filters( $type, $key );
}