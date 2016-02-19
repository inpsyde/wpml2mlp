<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Class UserPassThroughFilter
 *
 * @package W2M\Import\Filter
 */
class UserPassThroughFilter implements UserImportFilterInterface {

	/**
	 * Checks if a user should be imported or not
	 *
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return bool
	 */
	public function user_to_import( Type\ImportUserInterface $import_user ) {

		return TRUE;
	}

}