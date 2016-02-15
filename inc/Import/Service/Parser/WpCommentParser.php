<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Parser;

use
	W2M\Import\Type,
	W2M\Import\Common,
	SimpleXMLElement,
	WP_Error,
	DateTime;

/**
 * Class WpCommentParser
 *
 * @package W2M\Import\Service\Parser
 */
class WpCommentParser implements CommentParserInterface {

	/**
	 * @var string
	 */
	private $item = 'comment';

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
	 * @return Type\ImportCommentInterface|NULL
	 */
	public function parse_comment( SimpleXMLElement $document ) {

		$namespaces = $document->getDocNamespaces( TRUE );
		if ( ! isset( $namespaces[ 'wp' ] ) ) {
			$this->missing_namespace_error( $document );
			return;
		}

		$wp   = $document->children( $namespaces[ 'wp' ] );
		$item = $this->item;
		if ( ! isset( $document->{$item} ) ) {
			$this->missing_item_error( $document );
			return;
		}

		$comment_data = [];
		$attributes = [
			'comment_ID'           => 'origin_id',
			'comment_post_ID'      => 'origin_post_id',
			'comment_author'       => 'author_name',
			'comment_author_email' => 'author_email',
			'comment_author_url'   => 'author_url',
			'comment_author_IP'    => 'author_ip',
			'comment_date_gmt'     => 'date',
			'comment_content'      => 'content',
			'comment_karma'        => 'karma',
			'comment_approved'     => 'approved',
			'comment_agent'        => 'agent',
			'comment_type'         => 'type',
			'comment_parent'       => 'origin_parent_comment_id',
			'user_id'              => 'origin_user_id'
		];

		foreach ( $attributes as $node => $method ) {
			if ( ! isset( $wp->{$item}->{$node} ) ) {
				$this->missing_attribute_error( $document, $node );
				continue;
			}
			if ( 'date' === $method ) {
				// Todo: validate DateTime instance (#38)
				$date = DateTime::createFromFormat(
					'Y-m-d H:i:s',
					(string) $wp->{$item}->{$node}
				);
				if ( ! $date )
					$date = new DateTime();
				$comment_data[ $method ] = $date;
				continue;
			}
			$comment_data[ $method ] = $wp->{$item}->$node;
		}

		return new Type\WpImportComment( $comment_data );
	}

	/**
	 * @param SimpleXMLElement $document
	 * @param string $namespace
	 */
	private function missing_namespace_error( SimpleXMLElement $document, $namespace = 'wp' ) {

		$error = $this->wp_factory->wp_error(
			'namespace',
			"Missing namespace '{$namespace}' in XML comment node"
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
	 * Creating a missing item error
	 *
	 * @param SimpleXMLElement $document
	 * @param string $item
	 */
	private function missing_item_error( SimpleXMLElement $document, $item = 'comment' ) {

		$error = $this->wp_factory->wp_error(
			'item',
			"Missing item node '{$item}' in XML comment node"
		);
		$error->add_data(
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document' => $document,
					'item'     => $item
				)
			),
			'item'
		);

		$this->propagate_error( $error );
	}

	/**
	 * Creating a missing attribute error
	 *
	 * @param SimpleXMLElement $document
	 * @param $attribute
	 */
	private function missing_attribute_error( SimpleXMLElement $document, $attribute ) {

		$error = $this->wp_factory->wp_error(
			'attribute',
			"Missing attribute node '{$attribute}' in XML comment node"
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
	 * Fires the action w2m_import_parse_comment_error
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_parse_comment_error', $error );
	}

}