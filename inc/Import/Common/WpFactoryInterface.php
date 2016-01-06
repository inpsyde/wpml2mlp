<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

use
	WP_Error;

interface WpFactoryInterface {

	/**
	 * @param string $code
	 * @param string $message
	 * @param string $data
	 *
	 * @return WP_Error
	 */
	public function wp_error( $code = '', $message = '', $data = '' );
}