<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type;

interface CommentImporterInterface {


	/**
	 * @param Type\ImportCommentInterface $comment
	 *
	 * @return bool|\WP_Error
	 */
	public function import_comment( Type\ImportCommentInterface $comment );
}