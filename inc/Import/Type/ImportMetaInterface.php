<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportMetaInterface
 *
 * @package W2M\Import\Type
 */
interface ImportMetaInterface {

	/**
	 * @return string
	 */
	public function key();

	/**
	 * If is_single() is FALSE, the method will always return an array
	 * and it should considered as list of single post-meta values.
	 *
	 * @return array|scalar
	 */
	public function value();

	/**
	 * @return bool
	 */
	public function is_single();
}