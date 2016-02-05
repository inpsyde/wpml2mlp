<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface AncestorRelationInterface
 *
 * Todo:
 * The name of this interface is a bit to specific as it can
 * reflect any type of hierarchical n:1 relation type like
 * comment→post or post→post_parent. The method 'parent_id'
 * could be renamed to 'superior_id' to accommodate this fact
 *
 * @package W2M\Import\Type
 */
interface AncestorRelationInterface {

	/**
	 * @return int
	 */
	public function parent_id();

	/**
	 * @return int
	 */
	public function id();

	/**
	 * Ideally this method would be obsolete. It's a temporary hack to
	 * resolve the necessity for dealing with taxonomies as term_ids are
	 * not considered unique by WP core. We can resolve this method when
	 * dealing with term_taxonomy_ids globally.
	 *
	 * @return string
	 */
	public function type();
}