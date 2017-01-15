<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Common,
	W2M\Import\Data,
	W2M\Import\Type,
	DateTime;

/**
 * Class JsonFileImportReport
 *
 * Writes file import report in JSON format
 *
 * @package W2M\Import\Module
 */
class JsonFileImportReport implements FileImportReporterInterface {

	/**
	 * @var Type\FileImportReportInterface
	 */
	private $report;

	/**
	 * @var Common\FileInterface
	 */
	private $file;

	/**
	 * @param Type\FileImportReportInterface $report
	 * @param Common\FileInterface $file
	 */
	public function __construct(
		Type\FileImportReportInterface $report,
		Common\FileInterface $file
	) {

		$this->report = $report;
		$this->file   = $file;
	}

	/**
	 * @wp-hook w2m_import_process_done
	 *
	 * @param Type\ImportReportInterface $report
	 */
	public function create_report( Type\ImportReportInterface $report = NULL ) {

		/**
		 * We use the internal dependency here, the parameter comes just
		 * from the wp-hook and is not used, however.
		 */
		$this->report->memory_usage( memory_get_peak_usage( TRUE ) );
		$this->report->runtime( new DateTime );

		$this->file->set_content(
			json_encode( $this->report, JSON_PRETTY_PRINT )
		);

		/**
		 * @param Common\FileInterface $this->file
		 */
		do_action( 'w2m_import_json_report_created', $this->file );
	}

}