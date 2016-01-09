<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	W2M\Import\Common,
	SimpleXMLElement,
	WP_Error;

class WpTermParser implements TermParserInterface {

	/**
	 * @var string
	 */
	private $item = 'category';

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
	 * @param SimpleXMLElement $term
	 *
	 * @return Type\ImportTermInterface|NULL
	 */
	public function parse_term( SimpleXMLElement $term ) {

		// WP terms comes with namespaces elements
		$namespaces = $term->getDocNamespaces();
		if ( ! isset( $namespaces[ 'wp' ] ) ) {
			$this->missing_namespace_error( $term );
			return;
		}
		$wp_ns    =  $namespaces[ 'wp' ];
		$wp_terms = $term->children( $wp_ns );
		$item     = $this->item;

		if ( ! isset( $wp_terms->{$item} ) ) {
			$this->missing_item_error( $term, $item );
			return;
		}

		$term_data = array();
		// map node names to type attributes
		$attributes = array(
			'term_id'              => 'origin_id',
			'taxonomy'             => 'taxonomy',
			'category_nicename'    => 'slug',
			'category_parent'      => 'origin_parent_term_id',
			'cat_name'             => 'name',
			'category_description' => 'description'
			// Todo: Locale relations
		);

		foreach ( $attributes as $node => $method ) {
			if ( ! isset( $wp_terms->{$item}->{$node} ) ) {
				$this->missing_attribute_error( $term, $node );
				continue;
			}
			$term_data[ $method ] = $wp_terms->category->{$node};
		}

		if ( empty( $term_data[ 'taxonomy' ] ) || empty( $term_data[ 'name' ] ) ) {
			// at least these two attributes are required
			return;
		}

		return new Type\WpImportTerm( $term_data );
	}

	/**
	 * Creating a missing namespace error
	 *
	 * @param SimpleXMLElement $term
	 * @param string $namespace
	 */
	private function missing_namespace_error( SimpleXMLElement $term, $namespace = 'wp' ) {

		$error = $this->wp_factory->wp_error(
			'namespace',
			"Missing namespace '{$namespace}' in XML document"
		);
		$error->add_data(
			'namespace',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $term,
					'namespace' => $namespace
				)
			)
		);

		$this->propagate_error( $error );
	}

	/**
	 * Creating a missing item error
	 *
	 * @param SimpleXMLElement $term
	 * @param string $item
	 */
	private function missing_item_error( SimpleXMLElement $term, $item = 'category' ) {

		$error = $this->wp_factory->wp_error(
			'item',
			"Missing item node '{$item}' in XML document"
		);
		$error->add_data(
			'item',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document' => $term,
					'item'     => $item
				)
			)
		);

		$this->propagate_error( $error );
	}

	/**
	 * Creating a missing attribute error
	 *
	 * @param SimpleXMLElement $term
	 * @param $attribute
	 */
	private function missing_attribute_error( SimpleXMLElement $term, $attribute ) {

		$error = $this->wp_factory->wp_error(
			'attribute',
			"Missing attribute node '{$attribute}' in XML document"
		);
		$error->add_data(
			'attribute',
			array(
				'trigger' => __CLASS__,
				'data'    => array(
					'document'  => $term,
					'attribute' => $attribute
				)
			)
		);

		$this->propagate_error( $error );
	}

	/**
	 * Fires the action w2m_import_parse_term_error
	 *
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * For error loggers
		 *
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_parse_term_error', $error );
	}

}