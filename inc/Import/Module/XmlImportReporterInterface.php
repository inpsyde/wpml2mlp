<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data;

interface XmlImportReporterInterface {

	/**
	 * @param Data\XmlImportInterface $import
	 */
	public function create_report( Data\XmlImportInterface $import );
}