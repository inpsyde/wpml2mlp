<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Class AncestorRelation
 *
 * @package W2M\Import\Type
 */
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
	 * @var string
	 */
	private $type = '';

	/**
	 * @param int $parent_id
	 * @param int $id
	 * @param string $type (Optional)
	 */
	public function __construct( $parent_id, $id, $type = '' ) {

		$this->parent_id = (int) $parent_id;
		$this->id        = (int) $id;
		$this->type      = (string) $type;
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

	/**
	 * Ideally this method would be obsolete. It's a temporary hack to
	 * resolve the necessity for dealing with taxonomies as term_ids are
	 * not considered unique by WP core. We can resolve this method when
	 * dealing with term_taxonomy_ids globally.
	 *
	 * @return string
	 */
	public function type() {

		return $this->type;
	}

}