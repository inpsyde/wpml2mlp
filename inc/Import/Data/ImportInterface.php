<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	DateTime;

/**
 * Interface ImportInterface
 *
 * Describes an object with general information about an import
 *
 * @package W2M\Import\Data
 */
interface ImportInterface {

	/**
	 * @return DateTime
	 */
	public function start_date();

	/**
	 * @return int
	 */
	public function import_blog_id();
}