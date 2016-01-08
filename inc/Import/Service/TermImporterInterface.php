<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type;

interface TermImporterInterface {

	/**
	 * @param Type\ImportTermInterface $term
	 *
	 * @return bool|\WP_Error
	 */
	public function import_term( Type\ImportTermInterface $term );

}