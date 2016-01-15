<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Comment,
	WP_Post,
	WP_Term,
	WP_User,
	stdClass;

interface IdObserverInterface {

	/**
	 * @wp-hook w2m_comment_imported
	 *
	 * @param WP_Comment|stdClass $wp_comment
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return void
	 */
	public function record_comment( $wp_comment, Type\ImportCommentInterface $import_comment );

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function record_post( WP_Post $wp_post, Type\ImportPostInterface $import_post );

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param WP_Term|stdClass $wp_term
	 * @param Type\ImportPostInterface|Type\ImportTermInterface $import_term
	 *
	 * @return
	 */
	public function record_term( $wp_term, Type\ImportTermInterface $import_term );

	/**
	 * @wp-hook w2m_user_imported
	 *
	 * @param WP_User $wp_user
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return void
	 */
	public function record_user( WP_User $wp_user, Type\ImportUserInterface $import_user );

}