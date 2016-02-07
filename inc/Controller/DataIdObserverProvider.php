<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import\Data;

/**
 * Class DataIdObserverProvider
 *
 * This controller provides the IdMapper to »observe« the imported elements.
 *
 * @package W2M\Controller
 *
 * Todo: #54 Refactor IdObservableInterface dependency, see https://github.com/inpsyde/wpml2mlp/issues/54
 */
class DataIdObserverProvider {

	/**
	 * @var Data\ImportListeningTypeIdMapper
	 */
	private $id_mapper;

	/**
	 * @var Data\ImportListeningMTAncestorList
	 */
	private $unresolved_ancestor_mapper;

	/**
	 * @param Data\IdObserverInterface $id_mapper
	 * @param Data\ImportListeningMTAncestorList $unresolved_ancestor_list
	 */
	public function __construct(
		Data\IdObserverInterface $id_mapper,
		Data\ImportListeningMTAncestorList $unresolved_ancestor_list
	) {

		$this->id_mapper                  = $id_mapper;
		$this->unresolved_ancestor_mapper = $unresolved_ancestor_list;
	}

	/**
	 * Provide action handler
	 */
	public function register_id_observer() {

		add_action( 'w2m_import_set_comment_id', [ $this->id_mapper, 'record_comment' ] );
		add_action( 'w2m_import_set_post_id',    [ $this->id_mapper, 'record_post' ] );
		add_action( 'w2m_import_set_term_id',    [ $this->id_mapper, 'record_term' ] );
		add_action( 'w2m_import_set_user_id',    [ $this->id_mapper, 'record_user' ] );

		add_action(
			'w2m_import_missing_comment_ancestor',
			[ $this->unresolved_ancestor_mapper, 'record_comment_ancestor' ],
			10,
			2
		);
		add_action(
			'w2m_import_missing_post_ancestor',
			[ $this->unresolved_ancestor_mapper, 'record_post_ancestor' ],
			10,
			2
		);
		add_action(
			'w2m_import_missing_term_ancestor',
			[ $this->unresolved_ancestor_mapper, 'record_term_ancestor' ],
			10,
			2
		);
	}
}