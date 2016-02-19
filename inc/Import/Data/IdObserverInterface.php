<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type;

/**
 * Interface IdObserverInterface
 *
 * This interface describes an »observer« that listens to all imported
 * entities. The main use case is an IdMapper
 *
 * @package W2M\Import\Data
 * @deprecated
 *
 * Todo: #54 Resolve this interface, see https://github.com/inpsyde/wpml2mlp/issues/54
 */
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
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return void
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