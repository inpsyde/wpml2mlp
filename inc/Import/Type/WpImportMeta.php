<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

class WpImportMeta implements ImportMetaInterface {

	/**
	 * @var string
	 */
	private $key = '';

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @var bool
	 */
	private $is_single = TRUE;

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param bool $is_single (Optional, default to TRUE)
	 */
	public function __construct( $key, $value, $is_single = TRUE ) {

		$this->key = (string) $key;
		$this->value = $value;
		$this->is_single = (bool) $is_single;
	}

	/**
	 * @return string
	 */
	public function key() {

		return $this->key;
	}

	/**
	 * @return mixed
	 */
	public function value() {

		return $this->value;
	}

	/**
	 * @return bool
	 */
	public function is_single() {

		return $this->is_single;
	}

}