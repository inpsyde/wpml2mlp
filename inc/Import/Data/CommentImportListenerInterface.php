<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Comment;

/**
 * Interface CommentImportListenerInterface
 *
 * Describes a listener to w2m_comment_imported
 *
 * @package W2M\Import\Data
 */
interface CommentImportListenerInterface {

	/**
	 * @wp-hook w2m_comment_imported
	 *
	 * @param WP_Comment $wp_comment
	 * @param Type\ImportCommentInterface $import_comment
	 */
	public function record_comment( WP_Comment $wp_comment, Type\ImportCommentInterface $import_comment );
}