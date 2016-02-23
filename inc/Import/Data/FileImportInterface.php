<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Interface FileImportInterface
 *
 * Describes an import information object with additional
 * data for a import based on file (XML)
 *
 * @package W2M\Import\Data
 */
interface FileImportInterface extends ImportInterface {

	/**
	 * @return string
	 */
	public function import_file();

	/**
	 * The file used for id mapping (Optional)
	 *
	 * @return string
	 */
	public function map_file();
}