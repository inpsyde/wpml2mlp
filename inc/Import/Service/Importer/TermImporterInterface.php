<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Type;

/**
 * Interface TermImporterInterface
 *
 * @package W2M\Import\Service\Importer
 */
interface TermImporterInterface {

	/**
	 * @param Type\ImportTermInterface $term
	 *
	 * @return bool|\WP_Error
	 */
	public function import_term( Type\ImportTermInterface $term );

}