<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Comment,
	WP_Post,
	WP_Term,
	stdClass;

/**
 * Class ImportListeningMTAncestorList
 *
 * Listens to specific hooks to record
 * unresolvable ancestor-descendant relations with their **origin ids**!
 *
 * Todo: consider a better name for this. Maybe `UnresolvedAncestorRelations`
 *
 * Todo: Specify an interface for the recording methods
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
	private $relations = [];

	public function __construct() {

		$this->relations = [
			'comment' => [],
			'post'    => [],
			'term'    => []
		];
	}

	/**
	 * @param string $type ('post'|'term')
	 *
	 * @return Type\AncestorRelationInterface[] (Referring to origin_ids!)
	 */
	public function relations( $type ) {

		return isset( $this->relations[ $type ] )
			? array_values( $this->relations[ $type ] )
			: array();
	}

	/**
	 * @wp-hook w2m_import_missing_comment_ancestor
	 *
	 * @param stdClass|WP_Comment $comment
	 * @param Type\ImportCommentInterface $import_comment
	 */
	public function record_comment_ancestor( $comment, Type\ImportCommentInterface $import_comment ) {

		if ( !$import_comment->origin_parent_comment_id() ) {
			return;
		}

		$relation = new Type\AncestorRelation(
			$import_comment->origin_parent_comment_id(),
			$import_comment->origin_id()
		);
		$key      = "{$import_comment->origin_parent_comment_id()}:{$import_comment->origin_id()}";

		$this->relations[ 'comment' ][ $key ] = $relation;
	}

	/**
	 * @wp-hook w2m_import_missing_post_ancestor
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post_ancestor( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		if ( !$import_post->origin_parent_post_id() ) {
			return;
		}

		$relation = new Type\AncestorRelation(
			$import_post->origin_parent_post_id(),
			$import_post->origin_id()
		);
		$key      = "{$import_post->origin_parent_post_id()}:{$import_post->origin_id()}";

		$this->relations[ 'post' ][ $key ] = $relation;
	}

	/**
	 * @wp-hook w2m_import_missing_term_ancestor
	 *
	 * @param stdClass|WP_Term $wp_term
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record_term_ancestor( $wp_term, Type\ImportTermInterface $import_term ) {

		if ( !$import_term->origin_parent_term_id() ) {
			return;
		}

		$relation = new Type\AncestorRelation(
			$import_term->origin_parent_term_id(),
			$import_term->origin_id(),
			$wp_term->taxonomy
		);
		$key      = "{$import_term->origin_parent_term_id()}:{$import_term->origin_id()}:{$wp_term->taxonomy}";

		$this->relations[ 'term' ][ $key ] = $relation;
	}
}