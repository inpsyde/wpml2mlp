<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

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