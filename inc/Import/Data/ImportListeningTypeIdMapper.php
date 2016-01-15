<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	stdClass,
	WP_Term,
	WP_User,
	WP_Post;
use WP_Comment;

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
 */
class ImportListeningTypeIdMapper implements MultiTypeIdMapperInterface, IdObserverInterface {

	/**
	 * @var array {
	 *      [type] => array {
	 *          [origin_id] => [local_id]
	 *      }
	 * }
	 */
	private $map = array();

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
	 * @wp-hook w2m_term_imported
	 *
	 * @param stdClass|WP_Term $wp_term
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record_term( $wp_term, Type\ImportTermInterface $import_term ) {

		if ( isset( $this->map[ 'term' ] ) ) {
			$this->map[ 'term' ] = array();
		}

		$this->map[ 'term' ][ $import_term->origin_id() ] = (int) $wp_term->term_id;
	}

	/**
	 * @wp-hook w2m_user_imported
	 *
	 * @param WP_User $wp_user
	 * @param Type\ImportUserInterface $import_user
	 */
	public function record_user( WP_User $wp_user, Type\ImportUserInterface $import_user ) {

		if ( ! isset( $this->map[ 'user' ] ) ) {
			$this->map[ 'user' ] = array();
		}

		$this->map[ 'user' ][ $import_user->origin_id() ] = (int) $wp_user->ID;
	}

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		if ( ! isset( $this->map[ 'post' ] ) ) {
			$this->map[ 'post' ] = array();
		}

		$this->map[ 'post' ][ $import_post->origin_id() ] = (int) $wp_post->ID;
	}

	/**
	 * @wp-hook w2m_comment_imported
	 *
	 * @param WP_Comment|stdClass $wp_comment
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return void
	 */
	public function record_comment( $wp_comment, Type\ImportCommentInterface $import_comment ) {
		// TODO: Implement record_comment() method.
	}

}