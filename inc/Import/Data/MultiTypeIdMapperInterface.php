<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

interface MultiTypeIdMapperInterface {

	/**
	 * @param string $type
	 * @param int $local_id
	 * @return int
	 */
	public function remote_id( $type, $local_id );

	/**
	 * @param string $type
	 * @param int $remote_id
	 * @return int
	 */
	public function local_id( $type, $remote_id );
}