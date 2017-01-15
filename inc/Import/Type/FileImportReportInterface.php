<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common,
	JsonSerializable;

/**
 * Interface FileImportReportInterface
 *
 * Describes an import report based on an external file data source (XML)
 *
 * @package W2M\Import\Type
 */
interface FileImportReportInterface extends ImportReportInterface, JsonSerializable {

	/**
	 * @return Common\FileInterface The file with the import data
	 */
	public function import_file();

	/**
	 * @return Common\FileInterface|NULL The file that was used as id-map
	 */
	public function map_file();
}