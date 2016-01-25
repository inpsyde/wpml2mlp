<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Interface MultiTypeIdListInterface
 *
 * This list can return the complete stored data (id map) by type
 *
 * @package W2M\Import\Data
 */
interface MultiTypeIdListInterface {

	/**
	 * Returns a list of origin->local ID pairs
	 *
	 * @param $type
	 *
	 * @return array {
	 *      int [origin_id] => int [local_id]
	 * }
	 */
	public function id_map( $type );
}