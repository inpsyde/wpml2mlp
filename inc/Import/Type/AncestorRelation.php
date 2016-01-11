<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

class AncestorRelation implements AncestorRelationInterface {

	/**
	 * @var int
	 */
	private $id = 0;

	/**
	 * @var int
	 */
	private $parent_id = 0;

	/**
	 * @param int $parent_id
	 * @param int $id
	 */
	public function __construct( $parent_id, $id ) {

		$this->parent_id = (int) $parent_id;
		$this->id        = (int) $id;
	}

	/**
	 * @return int
	 */
	public function parent_id() {

		return $this->parent_id;
	}

	/**
	 * @return int
	 */
	public function id() {

		return $this->id;
	}

}