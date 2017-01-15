<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Error;

/**
 * Interface ImporterErrorInterface
 *
 * @package W2M\Log\Recorder
 */
interface ImporterErrorInterface {

	/**
	 * @param WP_Error $error
	 * @param Type\ImportElementInterface $import_element
	 *
	 * @return mixed
	 */
	public function record( WP_Error $error, Type\ImportElementInterface $import_element );
}