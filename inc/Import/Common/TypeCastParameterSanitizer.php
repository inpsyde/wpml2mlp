<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

/**
 * Class TypeCastParameterSanitizer
 *
 * @package W2M\Import\Common
 */
class TypeCastParameterSanitizer implements ParameterSanitizerInterface {

	/**
	 * @var bool
	 */
	private $strip_unknown = TRUE;

	/**
	 * @param bool $strip_unknown_param Optional
	 */
	public function __construct( $strip_unknown_param = TRUE ) {

		$this->strip_unknown = (bool) $strip_unknown_param;
	}

	/**
	 * @param array $mask [
	 *      string $key => string $type
	 * ]
	 * @param array $parameter
	 * @param array $context_data (Optional context data)
	 *
	 * @return array
	 */
	public function sanitize_parameter( Array $mask, Array $parameter, Array $context_data = array() ) {

		$data = array();
		if ( ! $this->strip_unknown )
			$data = &$parameter;

		foreach ( $mask as $key => $type ) {
			if ( ! isset( $parameter[ $key ] ) )
				continue;
			$data[ $key ] = $this->type_cast(
				$parameter[ $key ],
				$type
			);
		}

		return $data;
	}

	/**
	 * @param array $objects
	 * @param string $type
	 * @param array $context_data (Optional)
	 *
	 * @return mixed
	 */
	public function sanitize_object_list( Array $objects, $type, Array $context_data = array() ) {

		foreach ( $objects as $key => $instance ) {
			if ( is_a( $instance, $type ) )
				continue;

			if ( $this->strip_unknown )
				unset( $objects[ $key ] );
			else
				$objects[ $key ] = NULL;
		}

		return $objects;
	}

	/**
	 * Todo: Define how to handle scalar to non-scalar transitions
	 *
	 * @param mixed $value
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function type_cast( $value, $type ) {

		switch ( $type ) {
			case 'int' :
				return (int) $value;
				break;

			case 'string' :
				return (string) $value;
				break;

			case 'bool' :
				return (bool) $value;
				break;

			case 'float' :
				return (float) $value;
				break;

			case 'array' :
				return (array) $value;
				break;

			case 'object' :
				return (object) $value;
				break;
		}
	}
}