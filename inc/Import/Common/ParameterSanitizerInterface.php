<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

/**
 * Interface ParameterSanitizerInterface
 *
 * Description of a sanitizer
 *
 * Todo: The interface is way to unspecific. The behavior is meant to be
 * influenced by the $context_data parameter which breaks encapsulation and
 * leads to less polymorphic structure.
 *
 * @package W2M\Import\Common
 */
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