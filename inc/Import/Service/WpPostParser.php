<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Common,
	W2M\Import\Type,
	SimpleXMLElement,
	WP_Error;

class WpPostParser implements PostParserInterface {

	/**
	 * @var string
	 */
	private $item = 'item';

	/**
	 * @var Common\WpFactoryInterface
	 */
	private $wp_factory;

	/**
	 * @param Common\WpFactoryInterface $wp_factory (Optional)
	 */
	public function __construct( Common\WpFactoryInterface $wp_factory = NULL ) {

		$this->wp_factory = $wp_factory
			? $wp_factory
			: new Common\WpFactory;
	}

	/**
	 * @param SimpleXMLElement $post
	 *
	 * @return Type\ImportPostInterface|NULL
	 */
	public function parse_post( SimpleXMLElement $post ) {

		$post_data = array();


	}

	/**
	 * Fires the action w2m_import_parse_post_error
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * For error loggers
		 *
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_parse_post_error', $error );
	}
}