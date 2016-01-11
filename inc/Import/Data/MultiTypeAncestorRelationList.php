<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type;

/**
 * Interface MultiTypeAncestorRelationList
 *
 * The AncestorQueue stores unresolved ancestor-descendant relations
 *
 * @package W2M\Import\Data
 */
interface MultiTypeAncestorRelationList {

	/**
	 * @param string $type
	 *
	 * @return array (List of Type\AncestorRelationInterface)
	 */
	public function relations( $type );
}