<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	DateTime;

class XmlImportInfo implements XmlImportInterface {

	/**
	 * @var string
	 */
	private $import_file;

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 * @var DateTime
	 */
	private $start_date;

	/**
	 * @var string
	 */
	private $map_file;

	/**
	 * @param string $import_file
	 * @param int $blog_id (Optional)
	 * @param DateTime $start_date (Optional)
	 * @param string $map_file (Optional
	 */
	public function __construct(
		$import_file,
		$blog_id = NULL,
		DateTime $start_date = NULL,
		$map_file = ''
	) {

		$this->import_file = (string) $import_file;
		$this->blog_id = $blog_id
			? (int) $blog_id
			: get_current_blog_id();
		$this->start_date = $start_date
			? $start_date
			: new DateTime;
		$this->map_file = $map_file
			? (string) $map_file
			: '';

	}

	/**
	 * @return DateTime
	 */
	public function start_date() {

		return $this->start_date;
	}

	/**
	 * @return int
	 */
	public function import_blog_id() {

		return $this->blog_id;
	}

	/**
	 * @return string
	 */
	public function import_file() {

		return $this->import_file;
	}

	/**
	 * The file used for id mapping (Optional)
	 *
	 * @return string
	 */
	public function map_file() {

		return $this->map_file;
	}

}