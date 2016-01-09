<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Common,
	W2M\Import\Type,
	SimpleXMLElement,
	DateTime,
	DateTimeZone,
	WP_Error,
	stdClass;

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

		if ( ! isset( $document->item ) ) {
			$this->missing_item_error( $document, 'item' );
			return;
		}

		// Todo: Validation, error handling
		$doc_ns = $document->getDocNamespaces();
		$namespaces = array( 'wp', 'excerpt', 'content' );
		foreach ( $namespaces as $ns ) {
			if ( isset( $doc_ns[ $ns ] ) )
				continue;
			$this->missing_namespace_error( $ns, $document );
		}

		$excerpt_ns = $doc_ns[ 'excerpt' ]
			? $document->item->children( $doc_ns[ 'excerpt' ] )
			: new stdClass;
		$content_ns = $doc_ns[ 'content' ]
			? $document->item->children( $doc_ns[ 'content' ] )
			: new stdClass;

		/* @type SimpleXMLElement $wp */
		$wp = $document->item->children( $doc_ns[ 'wp' ] );

		// basic attributes w/o namespace
		$basic_atts = array(
			'title' => 'title',
			'guid'  => 'guid',
			'link'  => 'origin_link'
		);
		foreach ( $basic_atts as $attribute => $object_param_name ) {
			if ( isset( $document->item->{$attribute} ) ) {
				$post_data[ $object_param_name ] = (string) $document->item->{$attribute};
			} else {
				$post_data[ $object_param_name ] = '';
				$this->missing_attribute_error( $document, $attribute );
			}
		}

		// <content:encoded/>
		if ( isset( $content_ns->encoded ) ) {
			$post_data[ 'content' ] = (string) $content_ns->encoded;
		} else {
			$post_data[ 'content' ] = '';
			$this->missing_attribute_error( $document, 'content:encoded' );
		}

		// <namespace:encoded/>
		if ( isset( $content_ns->encoded ) ) {
			$post_data[ 'excerpt' ] = (string) $excerpt_ns->encoded;
		} else {
			$post_data[ 'excerpt' ] = '';
			$this->missing_attribute_error( $document, 'excerpt:encoded' );
		}

		$string_cast = function( SimpleXMLElement $node ) {
			return (string) $node;
		};
		$int_cast = function( SimpleXMLElement $node ) {
			return (int) $node;
		};
		$bool_cast = function ( SimpleXMLElement $node ) {
			// boolean cast of an SimpleXMLObject would always be true
			return (bool) (string) $node;
		};
		// <wp:*/> attributes
		$wp_attributes = array(
			//<wp:post_id/>
			'post_id' => array(
				'cast' => $int_cast,
				// ImportPostInterface attribute name
				'attribute' => 'origin_id',
			),
			'comment_status' => array(
				'cast' => $string_cast,
				'attribute' => 'comment_status',
			),
			'ping_status' => array(
				'cast' => $string_cast,
				'attribute' => 'ping_status',
			),
			'post_name' => array(
				'cast' => $string_cast,
				'attribute' => 'name',
			),
			'status' => array(
				'cast' => $string_cast,
				'attribute' => 'status',
			),
			'post_parent' => array(
				'cast' => $string_cast,
				'attribute' => 'origin_parent_post_id',
			),
			'menu_order' => array(
				'cast' => $int_cast,
				'attribute' => 'menu_order',
			),
			'post_type' => array(
				'cast' => $string_cast,
				'attribute' => 'type',
			),
			'post_password' => array(
				'cast' => $string_cast,
				'attribute' => 'password',
			),
			'is_sticky' => array(
				'cast' => $bool_cast,
				'attribute' => 'is_sticky',
			)
		);
		foreach ( $wp_attributes as $node_name => $parameter ) {
			$object_param_name = $parameter[ 'attribute' ];
			$type_cast_cb = $parameter[ 'cast' ];

			if ( isset( $wp->{$node_name} ) ) {
				$post_data[ $object_param_name ] = $type_cast_cb( $wp->{$node_name} );
			} else {
				$post_data[ $object_param_name ] = $type_cast_cb( '' );
				$this->missing_attribute_error( $document, "wp:{$node_name}" );
			}
		}

		if ( isset( $wp->post_date_gmt ) ) {
			$post_data[ 'date' ] = DateTime::createFromFormat(
				'Y-m-d H:i:s',
				$wp->post_date_gmt,
				new DateTimeZone( 'UTC' )
			);
		} else {
			$post_data[ 'date' ] = new DateTime;
			$this->missing_attribute_error( $document, 'date' );
		}

		return new Type\WpImportPost( $post_data );
	}

	/**
	 * @param SimpleXMLElement $document
	 * @param $namespace
	 */
	private function missing_namespace_error( SimpleXMLElement $document, $namespace ) {

		$error = $this->wp_factory->wp_error(
			'namespace',
			"Missing namespace '{$namespace}' in XML post node"
		);
		$error->add_data(
			'namespace',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'namespace' => $namespace
				)
			)
		);

		$this->propagate_error( $error );
	}

	/**
	 * @param SimpleXMLElement $document
	 * @param string $item (Optional)
	 */
	private function missing_item_error( SimpleXMLElement $document, $item = 'item' ) {

		$error = $this->wp_factory->wp_error(
			'item',
			"Missing item node '{$item}' in XML post node"
		);
		$error->add_data(
			'item',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'item'      => $item
				)
			)
		);

		$this->propagate_error( $error );
	}

	private function missing_attribute_error( SimpleXMLElement $document, $attribute ) {

		$error = $this->wp_factory->wp_error(
			'item',
			"Missing attribute '{$attribute}' in XML post node"
		);
		$error->add_data(
			'item',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $document,
					'attribute' => $attribute
				)
			)
		);

		$this->propagate_error( $error );
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