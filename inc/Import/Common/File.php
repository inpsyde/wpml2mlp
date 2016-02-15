<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

/**
 * Class File
 *
 * Simple representation to a concrete file on the local file system
 *
 * @package W2M\Import\Common
 */
class File implements FileInterface {

	/**
	 * @var string
	 */
	private $file;

	/**
	 * @param string $file
	 */
	public function __construct( $file ) {

		$this->file = (string) $file;
	}

	/**
	 * @return string
	 */
	public function name() {

		return $this->file;
	}

	/**
	 * @return string
	 */
	public function get_content() {

		return file_get_contents( $this->file );
	}

	/**
	 * @param string $content
	 */
	public function set_content( $content ) {

		file_put_contents( $this->file, $content );
	}
}