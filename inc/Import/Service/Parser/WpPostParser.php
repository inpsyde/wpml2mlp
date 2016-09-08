<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Parser;

use
	W2M\Import\Common,
	W2M\Import\Type,
	SimpleXMLElement,
	DateTime,
	DateTimeZone,
	WP_Error;

/**
 * Class WpPostParser
 *
 * @package W2M\Import\Service\Parser
 */
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
	 * @var Common\SimpleXmlTools
	 */
	private $xml_tools;

	/**
	 * @param Common\WpFactoryInterface|NULL $wp_factory
	 * @param Common\SimpleXmlTools|NULL $xml_tools
	 */
	public function __construct(
		Common\WpFactoryInterface $wp_factory = NULL,
		Common\SimpleXmlTools $xml_tools = NULL
	) {

		$this->wp_factory = $wp_factory
			? $wp_factory
			: new Common\WpFactory;

		$this->xml_tools = $xml_tools
			? $xml_tools
			: new Common\SimpleXmlTools;
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

		$doc_ns = $document->getDocNamespaces( TRUE );
		$namespaces = array( 'wp', 'excerpt', 'content' );
		foreach ( $namespaces as $ns ) {
			if ( isset( $doc_ns[ $ns ] ) )
				continue;
			$this->missing_namespace_error( $document, $ns );
			return;
		}

		$excerpt_ns = $document->item->children( $doc_ns[ 'excerpt' ] );
		$content_ns = $document->item->children( $doc_ns[ 'content' ] );
		/* @type SimpleXMLElement $wp */
		$wp         = $document->item->children( $doc_ns[ 'wp' ] );

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

		$string_cast = function( $node ) {
			return (string) $node;
		};
		$int_cast = function( $node ) {
			return (int) $node;
		};
		$bool_cast = function ( $node ) {
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
			'post_author' => array(
				'cast' => $int_cast,
				'attribute' => 'origin_author_id'
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
			),
			'attachment_url' => array(
				'cast' => $string_cast,
				'attribute' => 'origin_attachment_url'
			)
		);
		$missing_attributes = array();
		foreach ( $wp_attributes as $node_name => $parameter ) {
			$object_param_name = $parameter[ 'attribute' ];
			$type_cast_cb = $parameter[ 'cast' ];

			if ( isset( $wp->{$node_name} ) ) {
				$post_data[ $object_param_name ] = $type_cast_cb( $wp->{$node_name} );
			} else {
				$post_data[ $object_param_name ] = $type_cast_cb( '' );
				$missing_attributes[ $node_name ] = $node_name;
			}
		}
		if ( 'attachment' !== $post_data[ 'type' ] && isset( $missing_attributes[ 'attachment_url' ] ) ) {
			unset( $missing_attributes[ 'attachment_url' ] );
		}

		foreach ( $missing_attributes as $node_name ) {
			$this->missing_attribute_error( $document, "wp:{$node_name}" );
		}

		if ( isset( $wp->post_date_gmt ) ) {
			// Todo: validate the DateTime instance (#38)
			$post_data[ 'date' ] = DateTime::createFromFormat(
				'Y-m-d H:i:s',
				$wp->post_date_gmt,
				new DateTimeZone( 'UTC' )
			);
		} else {
			$post_data[ 'date' ] = new DateTime;
			$this->missing_attribute_error( $document, 'date' );
		}

		$post_data[ 'terms' ]            = $this->parse_post_terms( $document );
		$post_data[ 'meta' ]             = $this->parse_post_meta( $document );
		$post_data[ 'locale_relations' ] = $this->parse_locale_relations( $document );

		return new Type\WpImportPost( $post_data );
	}

	/**
	 * Access to this method is public for better testability. It will not
	 * raise errors on missing namespaces or general XML structure errors.
	 *
	 * @param SimpleXMLElement $document
	 *
	 * @return array
	 */
	public function parse_post_terms( SimpleXMLElement $document ) {

		$terms = array();
		if ( ! isset( $document->item ) )
			return $terms;

		foreach ( $document->item->category as $term ) {
			if ( ! isset( $term[ 'domain' ] ) ) {
				$this->missing_attribute_error( $document, 'category[@domain]' );
				continue;
			}
			if ( ! isset( $term[ 'term_id' ] ) ) {
				$this->missing_attribute_error( $document, 'category[@term_id]' );
				continue;
			}

			$terms[] = new Type\WpTermReference(
				(int) $term[ 'term_id' ],
				(string) $term[ 'domain' ],
				(string) $term[ 'nicename' ]
			);
		}

		return $terms;
	}


	/**
	 * Access to this method is public for better testability. It will not
	 * raise errors on missing namespaces or general XML structure errors.
	 *
	 * @param SimpleXMLElement $document
	 *
	 * @return array
	 */
	public function parse_post_meta( SimpleXMLElement $document ) {

		$meta_data  = array();
		$wp_ns      = $this->xml_tools->get_doc_namespace( $document, 'wp' );
		if ( ! $wp_ns )
			return $meta_data;

		if ( ! isset( $document->item ) )
			return $meta_data;

		/* @type SimpleXMLElement $wp */
		$wp = $document->item->children( $wp_ns );
		foreach ( $wp->postmeta as $post_meta ) {
			if ( ! isset( $post_meta->meta_key ) ) {
				$this->missing_attribute_error( $document, 'wp:meta_key' );
				continue;
			}
			if ( ! isset( $post_meta->meta_value ) ) {
				$this->missing_attribute_error( $document, 'wp:meta_value' );
				continue;
			}
			$meta_key = (string) $post_meta->meta_key;
			$meta_value = maybe_unserialize( (string) $post_meta->meta_value );
			if ( ! isset( $meta_data[ $meta_key ] ) ) {
				$meta_data[ $meta_key ] = array(
					'values' => array( $meta_value ),
					'is_single' => TRUE
				);
				continue;
			}

			$meta_data[ $meta_key ][ 'values' ][] = $meta_value;
			if ( $meta_data[ $meta_key ][ 'is_single' ] )
				$meta_data[ $meta_key ][ 'is_single' ] = FALSE;
		}

		$meta_objects = array();
		foreach ( $meta_data as $key => $structure ) {
			$is_single = $structure[ 'is_single' ];
			$value = $is_single
				? current( $structure[ 'values' ] )
				: $structure[ 'values' ];
			$meta_objects[] = new Type\WpImportMeta( $key, $value, $is_single );
		}

		return $meta_objects;
	}

	/**
	 * Access to this method is public for better testability. It will not
	 * raise errors on missing namespaces or general XML structure errors.
	 *
	 * @param SimpleXMLElement $document
	 *
	 * @return array
	 */
	public function parse_locale_relations( SimpleXMLElement $document ) {

		$locale_relations  = array();
		$wp_ns             = $this->xml_tools->get_doc_namespace( $document, 'wp' );
		if ( ! $wp_ns )
			return $locale_relations;

		if ( ! isset( $document->item ) )
			return $locale_relations;

		/* @type SimpleXMLElement $wp */
		$wp = $document->item->children( $wp_ns );
		foreach ( $wp->translation as $translation ) {
			if ( ! isset( $translation->element_id ) ) {
				$this->missing_attribute_error( $document, 'wp:translation/wp:element_id' );
				continue;
			}
			if ( ! isset( $translation->locale ) ) {
				$this->missing_attribute_error( $document, 'wp:translation/wp:locale' );
				continue;
			}
			$locale_relations[] = new Type\LocaleRelation(
				(string) $translation->locale,
				(int) $translation->element_id
			);
		}

		return $locale_relations;
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
			"Missing namespace '{$namespace}' in XML post node"
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
			"Missing attribute '{$attribute}' in XML post node"
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