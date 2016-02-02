<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	DateTime;

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