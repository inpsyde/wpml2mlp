<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Interface XmlImportInterface
 *
 * Describes an import information object with additional
 * data for the XML import type.
 *
 * @package W2M\Import\Data
 */
interface XmlImportInterface extends ImportInterface {

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