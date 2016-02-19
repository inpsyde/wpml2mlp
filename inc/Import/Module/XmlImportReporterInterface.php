<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data;

/**
 * Interface XmlImportReporterInterface
 *
 * @package W2M\Import\Module
 */
interface XmlImportReporterInterface {

	/**
	 * @param Data\XmlImportInterface $import
	 */
	public function create_report( Data\XmlImportInterface $import );
}