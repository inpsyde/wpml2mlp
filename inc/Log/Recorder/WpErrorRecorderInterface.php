<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	WP_Error;

/**
 * Interface WpErrorRecorderInterface
 *
 * @package W2M\Log\Recorder
 */
interface WpErrorRecorderInterface {

	/**
	 * @param WP_Error $error
	 *
	 * @return void
	 */
	public function record( WP_Error $error );
}