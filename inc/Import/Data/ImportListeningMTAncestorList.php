<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Post,
	WP_Term,
	WP_User,
	stdClass;

/**
 * Class ImportListeningMTAncestorList
 *
 * Listens to specific hooks to record
 * unresolvable ancestor-descendant relations with their **origin ids**!
 *
 * @package W2M\Import\Data
 */
class ImportListeningMTAncestorList implements MultiTypeAncestorRelationListInterface {

	/**
	 * @var array {
	 *      [type] => array {
	 *          [key] => [Type\AncestorRelationInterface]
	 *      }
	 * }
	 */
	private $relations = array();

	public function __construct() {

		$this->relations = array(
			'post' => array(),
			'term' => array()
		);
	}

	/**
	 * @param string $type ('post'|'term')
	 *
	 * @return array (List of Type\AncestorRelationInterface with **origin_ids**!)
	 */
	public function relations( $type ) {

		return isset( $this->relations[ $type ] )
			? array_values( $this->relations[ $type ] )
			: array();
	}

	/**
	 * @wp-hook w2m_import_missing_post_ancestor
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post_ancestor( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		if ( ! $import_post->origin_parent_post_id() )
			return;

		$relation = new Type\AncestorRelation(
			$import_post->origin_parent_post_id(),
			$import_post->origin_id()
		);
		$key = "{$import_post->origin_parent_post_id()}:{$import_post->origin_id()}";

		$this->relations[ 'post' ][ $key ] = $relation;
	}

	/**
	 * @wp-hook w2m_import_missing_term_ancestor
	 *
	 * @param stdClass|WP_Term $wp_term
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record_term_ancestor( $wp_term, Type\ImportTermInterface $import_term ) {

		if ( ! $import_term->origin_parent_term_id() )
			return;

		$relation = new Type\AncestorRelation(
			$import_term->origin_parent_term_id(),
			$import_term->origin_id()
		);
		$key = "{$import_term->origin_parent_term_id()}:{$import_term->origin_id()}";

		$this->relations[ 'term' ][ $key ] = $relation;
	}
}