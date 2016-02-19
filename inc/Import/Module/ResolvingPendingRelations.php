<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data,
	W2M\Import\Service,
	W2M\Import\Type;

/**
 * Class ResolvingPendingRelations
 *
 * Todo: Make this whole complex of relation resolving more versatile.
 * Pending relations are not necessarily limited to parent-child relations.
 *
 * @package W2M\Import\Module
 */
class ResolvingPendingRelations {

	/**
	 * @var Data\MultiTypeAncestorRelationListInterface
	 */
	private $pending_relations;

	/**
	 * @var Service\RelationResolverInterface
	 */
	private $post_resolver;

	/**
	 * @var Service\RelationResolverInterface
	 */
	private $term_resolver;

	public function __construct(
		Data\MultiTypeAncestorRelationListInterface $pending_relations,
		Service\RelationResolverInterface $post_resolver,
		Service\RelationResolverInterface $term_resolver
	) {

		$this->pending_relations = $pending_relations;
		$this->post_resolver     = $post_resolver;
		$this->term_resolver     = $term_resolver;
	}

	/**
	 * Iterates over pending post parent relations and tries to resolve them
	 *
	 * @wp-hook w2m_import_posts_done
	 */
	public function resolving_posts() {

		do_action( 'w2m_import_post_ancestor_resolving_start' );
		foreach ( $this->pending_relations->relations( 'post' ) as $relation ) {
			$this->post_resolver->resolve_relation( $relation );
		}
	}

	/**
	 * Iterates over pending post parent relations and tries to resolve them
	 *
	 * @wp-hook w2m_import_terms_done
	 */
	public function resolving_terms() {

		do_action( 'w2m_import_term_ancestor_resolving_start' );
		foreach ( $this->pending_relations->relations( 'term' ) as $relation ) {
			$this->term_resolver->resolve_relation( $relation );
		}
	}
}