<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

interface FileInterface {

	/**
	 * @return string
	 */
	public function name();

	/**
	 * @return string
	 */
	public function get_content();

	/**
	 * @param string $content
	 */
	public function set_content( $content );
}