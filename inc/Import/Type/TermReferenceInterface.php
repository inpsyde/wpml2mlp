<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface TermReferenceInterface
 *
 * References a singe Term by the origin id and taxonomy
 *
 * @package W2M\Import\Type
 */
interface TermReferenceInterface {

	/**
	 * @return int
	 */
	public function origin_id();

	/**
	 * @return string
	 */
	public function taxonomy();


	/**
	 * @return string
	 */
	public function nicename();

}