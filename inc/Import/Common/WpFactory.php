<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

use
	WP_Error,
	WP_Query;

/**
 * Class WpFactory
 *
 * Simple factory for WP objects
 *
 * @package W2M\Import\Common
 */
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

	/**
	 * @param array|string $query
	 *
	 * @return WP_Query
	 */
	public function wp_query( $query ) {

		return $this->common_factory->create_object( 'WP_Query', [ $query ] );
	}

}