<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Class PostPassThroughFilter
 *
 * @package W2M\Import\Filter
 */
class PostPassThroughFilter implements PostImportFilterInterface {

	/**
	 * Checks if a post should be imported or not
	 *
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return bool
	 */
	public function post_to_import( Type\ImportPostInterface $import_post ) {

		return TRUE;
	}

}