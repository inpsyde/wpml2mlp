<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

interface ParameterSanitizerInterface {

	/**
	 * @param array $mask [
	 *      string $key => string $type
	 * ]
	 * @param array $parameter
	 * @param array $context_data (Optional context data)
	 *
	 * @return array
	 */
	public function sanitize_parameter( Array $mask, Array $parameter, Array $context_data = array() );
}