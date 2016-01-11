<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

interface AncestorRelationInterface {

	/**
	 * @return int
	 */
	public function parent_id();

	/**
	 * @return int
	 */
	public function id();
}