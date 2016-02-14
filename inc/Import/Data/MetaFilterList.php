<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Filter;

/**
 * Class MetaFilterList
 *
 * Manages a list of Filter\ValueFilterableInterface for each type:meta-key pair
 *
 * @package W2M\Import\Data
 */
class MetaFilterList implements MetaFilterListInterface {

	/**
	 * @var Filter\ValueFilterableInterface[][]
	 */
	private $filters;

	public function __construct() {

		$this->filters = [
			'comment' => [],
			'post'    => [],
			'term'    => [],
			'user'    => []
		];
	}

	/**
	 * @param string $type
	 * @param string $key
	 * @param Filter\ValueFilterableInterface $filter
	 *
	 * @return bool
	 */
	public function push_filter( $type, $key, Filter\ValueFilterableInterface $filter ) {

		$this->setup_list( $type, $key );
		$index = spl_object_hash( $filter );
		if ( isset( $this->filters[ $type ][ $key ][ $index ] ) )
			return FALSE;

		$this->filters[ $type ][ $key ][ $index ] = $filter;

		return TRUE;
	}

	/**
	 * @param string $type
	 * @param string $key
	 * @param Filter\ValueFilterableInterface $filter
	 *
	 * @return bool
	 */
	public function pop_filter( $type, $key, Filter\ValueFilterableInterface $filter ) {

		$this->setup_list( $type, $key );
		$index = spl_object_hash( $filter );
		if ( isset( $this->filters[ $type ][ $key ][ $index ] ) ) {
			unset( $this->filters[ $type ][ $key ][ $index ] );

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param string $type
	 * @param string $key
	 *
	 * @return Filter\ValueFilterableInterface[]
	 */
	public function get_filters( $type, $key ) {

		$this->setup_list( $type, $key );

		return isset( $this->filters[ $type ][ $key ] )
			? $this->filters[ $type ][ $key ]
			: [];
	}

	/**
	 * @param string $type
	 * @param string $key
	 */
	private function setup_list( $type, $key ) {

		if ( isset( $this->filters[ $type ][ $key ] ) )
			return;

		$this->filters[ $type ][ $key ] = [];
	}

}