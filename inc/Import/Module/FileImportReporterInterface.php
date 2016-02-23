<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data;

/**
 * Interface FileImportReporterInterface
 *
 * @package W2M\Import\Module
 */
interface FileImportReporterInterface {

	/**
	 * @param Data\FileImportInterface $import
	 */
	public function create_report( Data\FileImportInterface $import );
}