<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

/**
 * Interface CommonFactoryInterface
 *
 * Creates objects by given class name and constructor parameter
 *
 * @package W2M\Import\Common
 */
interface CommonFactoryInterface {

	/**
	 * Creates an object of the given class passing the parameter to the constructor
	 *
	 * @param $class
	 * @param array $parameter
	 *
	 * @return object of type $class
	 */
	public function create_object( $class, Array $parameter = array() );
}