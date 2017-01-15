<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Parser;

use
	W2M\Import\Type,
	W2M\Import\Common,
	WP_Error,
	SimpleXMLElement;

/**
 * Class WpUserParser
 *
 * @package W2M\Import\Service\Parser
 */
class WpUserParser implements UserParserInterface {

	/**
	 * @var Common\WpFactoryInterface
	 */
	private $wp_factory;

	/**
	 * @param Common\WpFactoryInterface|NULL $wp_factory
	 */
	public function __construct(
		Common\WpFactoryInterface $wp_factory = NULL
	) {

		$this->wp_factory = $wp_factory
			? $wp_factory
			: new Common\WpFactory;
	}

	/**
	 * @param SimpleXMLElement $document
	 *
	 * @return Type\ImportUserInterface|NULL
	 */
	public function parse_user( SimpleXMLElement $document ) {

		$doc_ns = $document->getDocNamespaces( TRUE );
		if ( ! isset( $doc_ns[ 'wp' ] ) ) {
			$this->missing_namespace_error( $document, 'wp' );
			return;
		}

		/* @type SimpleXMLElement $wp */
		$wp = $document->children( $doc_ns[ 'wp' ] );
		if ( ! isset( $wp->author ) ) {
			$this->missing_item_error( $document, 'wp:author' );
			return;
		}

		$attributes = array(
			'author_id'           => 'origin_id',
			'author_login'        => 'login',
			'author_email'        => 'email',
			'author_first_name'   => 'first_name',
			'author_last_name'    => 'last_name',
			'author_display_name' => 'display_name',
			'author_role'         => 'role'
		);

		$user_data = array();
		foreach ( $attributes as $node => $method ) {
			if ( ! isset( $wp->author->{$node} ) ) {
				$this->missing_attribute_error( $document, "wp:{$node}" );
				continue;
			}
			$value = 'origin_id' === $method
				? (int) $wp->author->{$node}
				: (string) $wp->author->{$node};

			$user_data[ $method ] = $value;
		}

		//these two are mandatory
		if ( ! isset( $user_data[ 'login' ] ) || ! isset( $user_data[ 'email' ] ) ) {
			return;
		}

		return new Type\WpImportUser( $user_data );
	}


	/**
	 * @param SimpleXMLElement $document
	 * @param string $item (Optional)
	 */
	private function missing_item_error( SimpleXMLElement $document, $item = 'item' ) {

		$error = $this->wp_factory->wp_error(
			'item',
			"Missing item node '{$item}' in XML user node"
		);
		$error->add_data(
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'item'      => $item
				)
			),
			'item'
		);

		$this->propagate_error( $error );
	}

	/**
	 * @param SimpleXMLElement $document
	 * @param $namespace
	 */
	private function missing_namespace_error( SimpleXMLElement $document, $namespace ) {

		$error = $this->wp_factory->wp_error(
			'namespace',
			"Missing namespace '{$namespace}' in XML user node"
		);
		$error->add_data(
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'namespace' => $namespace
				)
			),
			'namespace'
		);

		$this->propagate_error( $error );
	}

	/**
	 * @param SimpleXMLElement $document
	 * @param $attribute
	 */
	private function missing_attribute_error( SimpleXMLElement $document, $attribute ) {

		$error = $this->wp_factory->wp_error(
			'attribute',
			"Missing attribute '{$attribute}' in XML user node"
		);
		$error->add_data(
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'attribute' => $attribute
				)
			),
			'attribute'
		);

		$this->propagate_error( $error );
	}

	/**
	 * Fires the action w2m_import_parse_user_error
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		do_action( 'w2m_import_parse_user_error', $error );
	}
}