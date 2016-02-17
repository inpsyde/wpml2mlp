<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

class WpMetaRecordIndex implements MetaRecordIndexInterface {

	/**
	 * @var string
	 */
	private $type = '';

	/**
	 * @var string
	 */
	private $key = '';

	/**
	 * @var int
	 */
	private $object_id = 0;

	/**
	 * @var int
	 */
	private $index = 0;

	/**
	 * @param $key
	 * @param $object_id
	 * @param int $index
	 * @param string $type
	 */
	public function __construct( $key, $object_id, $index = 0, $type = 'post' ) {

		$this->key       = (string) $key;
		$this->object_id = (int) $object_id;
		$this->index     = (int) $index;
		$this->type      = (string) $type;
	}

	/**
	 * The type the meta data belongs to
	 *
	 * @return string
	 */
	public function type() {

		return $this->type;
	}

	/**
	 * @return int
	 */
	public function object_id() {

		return $this->object_id;
	}

	/**
	 * @return string
	 */
	public function key() {

		return $this->key;
	}

	/**
	 * @return int
	 */
	public function index() {

		return $this->index;
	}

}