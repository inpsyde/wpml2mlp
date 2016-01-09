<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Common,
	W2M\Import\Type,
	SimpleXMLElement,
	DateTime,
	DateTimeZone,
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
	 * @param SimpleXMLElement $document
	 *
	 * @return Type\ImportPostInterface|NULL
	 */
	public function parse_post( SimpleXMLElement $document ) {

		// Todo: Validation, error handling
		$namespaces = $document->getDocNamespaces();
		$excerpt    = $document->item->children( $namespaces[ 'excerpt' ] );
		$content    = $document->item->children( $namespaces[ 'content' ] );
		/* @type SimpleXMLElement $wp */
		$wp         = $document->item->children( $namespaces[ 'wp' ] );

		$post_data = array(
			'title'                 => (string) $document->item->title,
			'guid'                  => (string) $document->item->guid,
			'origin_link'           => (string) $document->item->link,
			'excerpt'               => (string) $excerpt->encoded,
			'content'               => (string) $content->encoded,
			'origin_id'             => (int)    $wp->post_id,
			'comment_status'        => (string) $wp->comment_status,
			'ping_status'           => (string) $wp->ping_status,
			'name'                  => (string) $wp->post_name,
			'status'                => (string) $wp->status,
			'origin_parent_post_id' => (int)    $wp->post_parent,
			'menu_order'            => (int)    $wp->menu_order,
			'type'                  => (string) $wp->post_type,
			'password'              => (string) $wp->post_password,
			// boolean cast of an SimpleXMLObject would always be true
			'is_sticky'             => (bool) (string) $wp->is_sticky
		);

		$post_data[ 'date' ] = DateTime::createFromFormat(
			'Y-m-d H:i:s',
			$wp->post_date_gmt,
			new DateTimeZone( 'UTC' )
		);

		return new Type\WpImportPost( $post_data );
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