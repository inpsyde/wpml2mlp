<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type;

/**
 * Class ImportListeningTypeIdMapper
 *
 * A single instance of this list is used by each importer to
 * resolve relations on the fly.
 *
 * Listens to the actions
 *
 *  - w2m_term_imported
 *  - w2m_post_imported
 *  - w2m_user_imported
 *
 * to track IDs of the types 'post', 'user' and 'term'
 *
 * @package W2M\Import\Data
 *
 * Todo: #54 Refactor IdObservableInterface implementation, see https://github.com/inpsyde/wpml2mlp/issues/54
 */
class ImportListeningTypeIdMapper implements MultiTypeIdMapperInterface, IdObserverInterface, MultiTypeIdListInterface {

	/**
	 * @var array {
	 *      [type] => array {
	 *          [origin_id] => [local_id]
	 *      }
	 * }
	 */
	private $map = [];

	/**
	 * Set up internal structures
	 */
	public function __construct() {

		$this->map = [
			'comment' => [],
			'post' => [],
			'term' => [],
			'user' => []
		];
	}

	/**
	 * @param string $type
	 * @param int $local_id
	 *
	 * @return int
	 */
	public function origin_id( $type, $local_id ) {

		if ( ! isset( $this->map[ $type ] ) )
			return 0;

		$origin_id = array_search( $local_id, $this->map[ $type ] );

		return (int) $origin_id;
	}

	/**
	 * @param string $type
	 * @param int $origin_id
	 *
	 * @return int
	 */
	public function local_id( $type, $origin_id ) {

		if ( ! isset( $this->map[ $type ] ) )
			return 0;

		if ( ! isset( $this->map[ $type ][ $origin_id ] ) )
			return 0;

		return $this->map[ $type ][ $origin_id ];
	}

	/**
	 * Returns a list of origin->local ID pairs
	 *
	 * @param $type
	 *
	 * @return array {
	 *      int [origin_id] => int [local_id]
	 * }
	 */
	public function id_map( $type ) {

		return isset( $this->map[ $type ] )
			? $this->map[ $type ]
			: [];
	}

	/**
	 * @wp-hook w2m_import_set_term_id
	 *
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record_term( Type\ImportTermInterface $import_term ) {

		$this->map[ 'term' ][ $import_term->origin_id() ] = $import_term->id();
	}

	/**
	 * @wp-hook w2m_import_set_user_id
	 *
	 * @param Type\ImportUserInterface $import_user
	 */
	public function record_user( Type\ImportUserInterface $import_user ) {

		$this->map[ 'user' ][ $import_user->origin_id() ] = $import_user->id();
	}

	/**
	 * @wp-hook w2m_import_set_post_id
	 *
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post( Type\ImportPostInterface $import_post ) {

		$this->map[ 'post' ][ $import_post->origin_id() ] = $import_post->id();
	}

	/**
	 * @wp-hook w2m_import_set_comment_id
	 *
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return void
	 */
	public function record_comment( Type\ImportCommentInterface $import_comment ) {

		$this->map[ 'comment' ][ $import_comment->origin_id() ] = $import_comment->id();
	}

}