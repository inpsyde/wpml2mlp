<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Class ImmutableMultiTypeIdList
 *
 * @todo Write tests for
 *
 * @package W2M\Import\Data
 */
class ImmutableMultiTypeIdList implements MultiTypeIdListInterface, MultiTypeIdMapperInterface {

	/**
	 * @var array
	 */
	private $maps = [];

	/**
	 * @param array $maps
	 */
	public function __construct( array $maps ) {

		foreach ( $maps as $type => $map ) {
			if ( ! is_array( $map ) )
				continue;
			$map = array_filter( 'intval', $map );
			$this->maps[ $type ] = $map;
		}
	}

	/**
	 * Returns a list of origin->local ID pairs
	 *
	 * @param $type
	 *
	 * @return array {
	 *      int [origin_id] => int [local_id]
	 * }
	 */
	public function id_map( $type ) {

		return isset( $this->maps[ $type ] )
			? $this->maps[ $type ]
			: [];
	}

	/**
	 * @param string $type
	 * @param int $local_id
	 *
	 * @return int
	 */
	public function origin_id( $type, $local_id ) {

		if ( ! $this->type_exists( $type ) )
			return 0;

		$origin_id = array_search( $local_id, $this->maps[ $type ] );

		return $origin_id;
	}

	/**
	 * @param string $type
	 * @param int $origin_id
	 *
	 * @return int
	 */
	public function local_id( $type, $origin_id ) {

		if ( ! $this->type_exists( $type ) )
			return 0;

		return isset( $this->maps[ $type ][ $origin_id ] )
			? $this->maps[ $type ][ $origin_id ]
			: 0;
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	private function type_exists( $type ) {

		return isset( $this->maps[ $type ] );
	}
}