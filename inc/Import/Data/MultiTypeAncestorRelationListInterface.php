<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type;

/**
 * Interface MultiTypeAncestorRelationListInterface
 *
 * This list stores unresolved ancestor-descendant relations
 *
 * @package W2M\Import\Data
 */
interface MultiTypeAncestorRelationListInterface {

	/**
	 * @param string $type
	 *
	 * @return Type\AncestorRelationInterface[] (Referring to origin_ids!)
	 */
	public function relations( $type );
}