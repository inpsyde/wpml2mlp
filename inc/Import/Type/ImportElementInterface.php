<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

interface ImportElementInterface {

	/**
	 * The id of the element in the original system
	 *
	 * @return int
	 */
	public function origin_id();

	/**
	 * Set the id of the imported object in the local system
	 *
	 * @param int $id
	 *
	 * @return int|void
	 */
	public function id( $id = 0 );
}