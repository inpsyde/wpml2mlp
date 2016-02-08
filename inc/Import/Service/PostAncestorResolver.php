<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Common,
	W2M\Import\Data,
	W2M\Import\Type,
	WP_Error;

/**
 * Class PostAncestorResolver
 *
 * @Todo: Write Tests for
 *
 * @package W2M\Import\Service
 */
class PostAncestorResolver implements RelationResolverInterface {

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
	 *
	 * @internal param int $element_id
	 * @internal param int $superior_id
	 */
	public function resolve_relation( Type\AncestorRelationInterface $relation ) {

		$parent_id   = $this->id_mapper->local_id( 'post', $relation->parent_id() );
		$parent_post = get_post( $parent_id );
		if ( ! $parent_post ) {
			$this->missing_parent_error( $relation );
			return;
		}

		$post_id = $this->id_mapper->local_id( 'post', $relation->id() );
		$post    = get_post( $post_id );
		if ( ! $post ) {
			$this->missing_post_error( $relation );
			return;
		}

		$post_data = [
			'ID'          => $post->ID,
			'post_parent' => $parent_post->ID
		];
		$result = wp_update_post( $post_data, TRUE );
		if ( is_wp_error( $result ) ) {
			$this->propagate_error( $result );
			return;
		}

		/**
		 * @param array {
		 *     Type\AncestorRelationInterface $relation
		 *     \WP_Post $post
		 *     \WP_Post $parent_post
		 * }
		 */
		do_action(
			'w2m_import_post_ancestor_resolved',
			[
				'relation'    => $relation,
				'post'        => $post,
				'parent_post' => $parent_post
			]
		);
	}

	/**
	 * do action 'w2m_import_post_ancestor_resolver_error'
	 *
	 * @param Type\AncestorRelationInterface $relation
	 */
	private function missing_parent_error( Type\AncestorRelationInterface $relation ) {

		$error = $this->factory->wp_error(
			'post_parent',
			'Resolver missing post parent',
			[
				'post_id'          => $this->id_mapper->local_id( 'post', $relation->id() ),
				'parent_id'        => $this->id_mapper->local_id( 'post', $relation->parent_id() ),
				'remote_id'        => $relation->id(),
				'remote_parent_id' => $relation->parent_id()
			]
		);

		$this->propagate_error( $error );
	}

	/**
	 * do action 'w2m_import_post_ancestor_resolver_error'
	 *
	 * @param Type\AncestorRelationInterface $relation
	 */
	private function missing_post_error( Type\AncestorRelationInterface $relation ) {

		$error = $this->factory->wp_error(
			'post',
			'Resolver missing post',
			[
				'post_id'          => $this->id_mapper->local_id( 'post', $relation->id() ),
				'parent_id'        => $this->id_mapper->local_id( 'post', $relation->parent_id() ),
				'remote_id'        => $relation->id(),
				'remote_parent_id' => $relation->parent_id()
			]
		);

		$this->propagate_error( $error );
	}

	/**
	 * do action 'w2m_import_post_ancestor_resolver_error'
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_post_ancestor_resolver_error', $error );
	}

}