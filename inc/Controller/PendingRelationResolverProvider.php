<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import\Module;

/**
 * Class PendingRelationResolverProvider
 *
 * Todo: Refactor the type hint on a concrete class.
 *
 * @package W2M\Controller
 */
class PendingRelationResolverProvider {

	/**
	 * @var Module\ResolvingPendingRelations
	 */
	private $resolver;

	/**
	 * @param Module\ResolvingPendingRelations $resolver
	 */
	public function __construct( Module\ResolvingPendingRelations $resolver ) {

		$this->resolver = $resolver;
	}

	public function register_resolver() {

		add_action(
			'w2m_import_posts_done',
			[ $this->resolver, 'resolving_posts' ]
		);
		add_action(
			'w2m_import_terms_done',
			[ $this->resolver, 'resolving_terms' ]
		);
	}
}