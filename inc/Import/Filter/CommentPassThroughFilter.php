<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Class CommentPassThroughFilter
 *
 * @package W2M\Import\Filter
 */
class CommentPassThroughFilter implements CommentImportFilterInterface {

	/**
	 * Checks if a comment should be imported or not
	 *
	 * @param Type\ImportCommentInterface $import_comment
	 *
	 * @return bool
	 */
	public function comment_to_import( Type\ImportCommentInterface $import_comment ) {

		return TRUE;
	}

}