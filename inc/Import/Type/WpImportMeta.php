<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Class WpImportMeta
 *
 * @package W2M\Import\Type
 */
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
	 * If is_single() is FALSE, the method will always return an array
	 * and it should considered as list of single post-meta values.
	 *
	 * @return array|scalar
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