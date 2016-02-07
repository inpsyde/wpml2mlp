<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Interface TermFilterInterface
 *
 * @package W2M\Import\Filter
 */
interface TermFilterInterface {

	/**
	 * Checks if a term should be imported or not
	 *
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return bool
	 */
	public function term_to_import( Type\ImportTermInterface $import_term );
}