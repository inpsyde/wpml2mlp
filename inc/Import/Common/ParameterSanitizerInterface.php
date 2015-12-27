<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

interface ParameterSanitizerInterface {

	/**
	 * @param array $mask {
	 *      string $key => string $type
	 * }
	 * @param array $parameter
	 * @param array $context_data (Optional context data)
	 *
	 * @return array
	 */
	public function sanitize_parameter( Array $mask, Array $parameter, Array $context_data = array() );

	/**
	 * @param array $objects
	 * @param string $type
	 * @param array $context_data (Optional)
	 *
	 * @return mixed
	 */
	public function sanitize_object_list( Array $objects, $type, Array $context_data = array() );
}