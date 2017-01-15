<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Common,
	W2M\Import\Type;

/**
 * Class PresetUserTypeIdMapper
 *
 * Wrapper for ImportListeningTypeIdMapper with pre-defined user mapping from a json-report file
 *
 * @package W2M\Import\Data
 *
 * Todo: #54 Refactor IdObservableInterface implementation, see https://github.com/inpsyde/wpml2mlp/issues/54
 */
class PresetUserTypeIdMapper implements MultiTypeIdMapperInterface, IdObserverInterface, MultiTypeIdListInterface  {

	/**
	 * @var ImportListeningTypeIdMapper
	 */
	private $mapper;

	/**
	 * @var array
	 */
	private $user_map = [];

	/**
	 * @param ImportListeningTypeIdMapper $mapper
	 * @param array $user_map
	 */
	public function __construct(
		ImportListeningTypeIdMapper $mapper,
		Array $user_map
	) {

		$this->mapper   = $mapper;
		$this->user_map = $user_map;
	}

	/**
	 * @wp-hook w2m_import_set_comment_id
	 *
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return void
	 */
	public function record_comment( Type\ImportCommentInterface $import_comment ) {

		$this->mapper->record_comment( $import_comment );
	}

	/**
	 * @wp-hook w2m_import_set_post_id
	 *
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function record_post( Type\ImportPostInterface $import_post ) {

		$this->mapper->record_post( $import_post );
	}

	/**
	 * @wp-hook w2m_import_set_term_id
	 *
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return void
	 */
	public function record_term( Type\ImportTermInterface $import_term ) {

		$this->mapper->record_term( $import_term );
	}

	/**
	 * @wp-hook w2m_import_set_user_id
	 *
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return void
	 */
	public function record_user( Type\ImportUserInterface $import_user ) {

		// nothing to do here
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

		if ( 'user' === $type )
			return $this->user_map;

		return $this->mapper->id_map( $type );
	}

	/**
	 * @param string $type
	 * @param int $local_id
	 *
	 * @return int
	 */
	public function origin_id( $type, $local_id ) {

		if ( 'user' !== $type )
			return $this->mapper->origin_id( $type, $local_id );

		$origin_id = array_search( $local_id, $this->user_map );

		return (int) $origin_id;
	}

	/**
	 * @param string $type
	 * @param int $origin_id
	 *
	 * @return int
	 */
	public function local_id( $type, $origin_id ) {

		if ( 'user' !== $type )
			return $this->mapper->local_id( $type, $origin_id );

		return isset( $this->user_map[ $origin_id ] )
			? (int) $this->user_map[ $origin_id ]
			: 0;
	}

}