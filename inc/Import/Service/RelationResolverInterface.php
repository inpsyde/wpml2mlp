<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type;

interface RelationResolverInterface {

	/**
	 * @param Type\AncestorRelationInterface $relation
	 */
	public function resolve_relation( Type\AncestorRelationInterface $relation );
}