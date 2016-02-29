<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Type;

/**
 * Interface FileImportReporterInterface
 *
 * @package W2M\Import\Module
 */
interface FileImportReporterInterface {

	/**
	 * creates the report
	 *
	 * @wp-hook w2m_import_process_done
	 *
	 * @param Type\ImportReportInterface $report
	 */
	public function create_report( Type\ImportReportInterface $report = NULL );
}