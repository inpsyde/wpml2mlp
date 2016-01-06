<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

use WP_Error;

class WpFactory implements WpFactoryInterface {

	/**
	 * @var CommonFactoryInterface
	 */
	private $common_factory;

	/**
	 * @param CommonFactoryInterface $common_factory (Optional)
	 */
	public function __construct( CommonFactoryInterface $common_factory = NULL ) {

		$this->common_factory = $common_factory
			? $common_factory
			: new CommonFactory;
	}

	/**
	 * @param string $code
	 * @param string $message
	 * @param string $data
	 *
	 * @return WP_Error
	 */
	public function wp_error( $code = '', $message = '', $data = '' ) {

		return $this->common_factory->create_object( 'WP_Error', func_get_args() );
	}
}