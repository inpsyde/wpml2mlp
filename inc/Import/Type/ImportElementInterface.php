<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportElementInterface
 *
 * @package W2M\Import\Type
 */
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
	 * If any parameter of type integer is passed, the method
	 * acts like a setter, otherwise it acts like a getter.
	 * It must return the ID value (integer) in any case.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function id( $id = 0 );
}