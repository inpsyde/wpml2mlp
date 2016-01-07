<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

interface ImportMetaInterface {

	/**
	 * @return string
	 */
	public function key();

	/**
	 * @return mixed
	 */
	public function value();

	/**
	 * @return bool
	 */
	public function is_single();
}