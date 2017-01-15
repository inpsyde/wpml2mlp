<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Interface MultiTypeIdMapperInterface
 *
 * Describes a single-item mapper
 *
 * @package W2M\Import\Data
 */
interface MultiTypeIdMapperInterface {

	/**
	 * @param string $type
	 * @param int $local_id
	 * @return int
	 */
	public function origin_id( $type, $local_id );

	/**
	 * @param string $type
	 * @param int $origin_id
	 *
	 * @return int
	 */
	public function local_id( $type, $origin_id );
}