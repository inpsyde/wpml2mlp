<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Common,
	W2M\Import\Data,
	W2M\Import\Type,
	WP_Error;

/**
 * Class TermAncestorResolver
 *
 * Todo: Write tests for
 *
 * @package W2M\Import\Service
 */
class TermAncestorResolver implements RelationResolverInterface {

	/**
	 * @var Common\WpFactoryInterface
	 */
	private $factory;

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param Common\WpFactoryInterface $factory (Optional)
	 */
	function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper,
		Common\WpFactoryInterface $factory = NULL
	) {

		$this->id_mapper = $id_mapper;
		$this->factory   = $factory
			? $factory
			: new Common\WpFactory;
	}

	/**
	 * @param Type\AncestorRelationInterface $relation
	 */
	public function resolve_relation( Type\AncestorRelationInterface $relation ) {

		$parent_id = $this->id_mapper->local_id( 'term', $relation->parent_id() );
		$parent    = get_term_by( 'term_id', $parent_id, $relation->type() );
		if ( ! $parent ) {
			$this->missing_parent_error( $relation );
			return;
		}

		$term_id = $this->id_mapper->local_id( 'term', $relation->id() );
		$term    = get_term_by( 'term_id', $term_id, $relation->type() );
		if ( ! $term ) {
			$this->missing_term_error( $relation );
			return;
		}

		$result = wp_update_term(
			$term->term_id,
			$term->taxonomy,
			[
				'parent' => $parent->term_id
			]
		);
		if ( is_wp_error( $result ) ) {
			$this->propagate_error( $result );
			return;
		}

		/**
		 * @param array {
		 *     Type\AncestorRelationInterface $relation
		 *     \WP_Term $term
		 *     \WP_Term $parent_term
		 * }
		 */
		do_action(
			'w2m_import_term_ancestor_resolved',
			[
				'relation'    => $relation,
				'term'        => $term,
				'parent_term' => $parent
			]
		);
	}

	/**
	 * do action 'w2m_import_term_ancestor_resolver_error'
	 *
	 * @param Type\AncestorRelationInterface $relation
	 */
	private function missing_parent_error( Type\AncestorRelationInterface $relation ) {

		$error = $this->factory->wp_error(
			'term_parent',
			'Resolver missing term parent',
			[
				'term_id'          => $this->id_mapper->local_id( 'term', $relation->id() ),
				'parent_id'        => $this->id_mapper->local_id( 'term', $relation->parent_id() ),
				'remote_id'        => $relation->id(),
				'remote_parent_id' => $relation->parent_id()
			]
		);

		$this->propagate_error( $error );
	}

	/**
	 * do action 'w2m_import_term_ancestor_resolver_error'
	 *
	 * @param Type\AncestorRelationInterface $relation
	 */
	private function missing_term_error( Type\AncestorRelationInterface $relation ) {

		$error = $this->factory->wp_error(
			'term',
			'Resolver missing term',
			[
				'term_id'          => $this->id_mapper->local_id( 'term', $relation->id() ),
				'parent_id'        => $this->id_mapper->local_id( 'term', $relation->parent_id() ),
				'remote_id'        => $relation->id(),
				'remote_parent_id' => $relation->parent_id()
			]
		);

		$this->propagate_error( $error );
	}

	/**
	 * do action 'w2m_import_term_ancestor_resolver_error'
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_term_ancestor_resolver_error', $error );
	}


}