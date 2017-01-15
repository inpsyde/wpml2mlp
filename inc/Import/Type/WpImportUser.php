<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common;

/**
 * Class WpImportUser
 *
 * @package W2M\Import\Type
 */
class WpImportUser implements ImportUserInterface {

	private $id = NULL;

	private $origin_id = 0;

	private $login = '';

	private $email = '';

	private $first_name = '';

	private $last_name = '';

	private $display_name = '';

	private $role = '';

	private $param_sanitizer;

	/**
	 * @param array $attributes {
	 *      int     $origin_id,
	 *      string  $login,
	 *      string  $email,
	 *      string  $first_name,
	 *      string  $last_name,
	 *      string  $display_name
	 * }
	 * @param Common\ParameterSanitizerInterface|NULL $param_sanitizer
	 */
	public function __construct(
		Array $attributes,
		Common\ParameterSanitizerInterface $param_sanitizer = NULL
	) {

		$this->param_sanitizer = $param_sanitizer
			? $param_sanitizer
			: new Common\TypeCastParameterSanitizer;

		$this->set_attributes( $attributes );
	}

	/**
	 * @param array $attributes
	 */
	private function set_attributes( Array $attributes ) {

		$type_map = array(
			'origin_id'    => 'int',
			'login'        => 'string',
			'email'        => 'string',
			'first_name'   => 'string',
			'last_name'    => 'string',
			'display_name' => 'string',
			'role' => 'string'
		);

		$valid_attributes = $this->param_sanitizer
			->sanitize_parameter( $type_map, $attributes );
		foreach ( $type_map as $key => $type ) {
			if ( ! isset( $valid_attributes[ $key ] ) )
				continue;
			$this->{ $key } = $valid_attributes[ $key ];
		}
	}

	/**
	 * The id of the element in the original system
	 *
	 * @return int
	 */
	public function origin_id() {

		return $this->origin_id;
	}

	/**
	 * Set the id of the imported object in the local system
	 * If any parameter of type integer is passed, the method
	 * acts like a setter, otherwise it acts like a getter.
	 * It must return the ID value (integer) in any case.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function id( $id = 0 ) {

		if ( ! is_null( $this->id ) || empty( $id ) ) {
			return (int) $this->id;
		}

		$this->id = (int) $id;

		/**
		 * This action allows an automated mapping of old/new
		 * element ids
		 *
		 * Todo: This hook is redundant to w2m_post_imported and should be removed
		 *
		 * @deprecated
		 * @param ImportPostInterface $this
		 */
		do_action( 'w2m_import_set_user_id', $this );
	}

	/**
	 * @return string
	 */
	public function login() {

		return $this->login;
	}

	/**
	 * @return string
	 */
	public function email() {

		return $this->email;
	}

	/**
	 * @return string
	 */
	public function first_name() {

		return $this->first_name;
	}

	/**
	 * @return string
	 */
	public function last_name() {

		return $this->last_name;
	}

	/**
	 * @return string
	 */
	public function display_name() {

		return $this->display_name;
	}

	/**
	 * @return string
	 */
	public function role() {

		return $this->role;
	}

}