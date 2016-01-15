<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type;

interface IdObserverInterface {

	/**
	 * @wp-hook w2m_import_set_comment_id
	 *
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return void
	 */
	public function record_comment( Type\ImportCommentInterface $import_comment );

	/**
	 * @wp-hook w2m_import_set_post_id
	 *
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function record_post( Type\ImportPostInterface $import_post );

	/**
	 * @wp-hook w2m_import_set_term_id
	 *
	 * @param Type\ImportPostInterface|Type\ImportTermInterface $import_term
	 *
	 * @return
	 */
	public function record_term( Type\ImportTermInterface $import_term );

	/**
	 * @wp-hook w2m_import_set_user_id
	 *
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return void
	 */
	public function record_user( Type\ImportUserInterface $import_user );

}