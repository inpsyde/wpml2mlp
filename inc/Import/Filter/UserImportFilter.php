<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Interface UserImportFilter
 *
 * @package W2M\Import\Filter
 */
interface UserImportFilter {

	/**
	 * Checks if a user should be imported or not
	 *
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return bool
	 */
	public function user_to_import( Type\ImportUserInterface $import_user );
}