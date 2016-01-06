<?php # -*- coding: utf-8 -*-

namespace W2M\Import\ObjectCreation;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * Class WpImportObjectBuilder
 *
 * @deprecated Use Service\*ParserInterface instead
 */
class WpImportObjectBuilder implements WpImportObjectBuilderInterface {

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportTermInterface
	 */
	public function build_import_term( SimpleXMLElement $element ) {

		$wp_ns = $this->get_wp_namespace( $element );
		if ( ! $wp_ns ) {
			// Todo: proper error handling (maybe exceptions)
			return NULL;
		}


	}

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportPostInterface
	 */
	public function build_import_post( SimpleXMLElement $element ) {

		$wp_ns = $this->get_wp_namespace( $element );
		if ( ! $wp_ns ) {
			// Todo: proper error handling (maybe exceptions)
			return NULL;
		}
	}

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportUserInterface
	 */
	public function build_import_user( SimpleXMLElement $element ) {

		$wp_ns = $this->get_wp_namespace( $element );
		if ( ! $wp_ns ) {
			// Todo: proper error handling (maybe exceptions)
			return NULL;
		}
	}

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return string
	 */
	public function get_wp_namespace( SimpleXMLElement $element ) {

		$namespaces = $element->getDocNamespaces();
		return isset( $namespaces[ 'wp' ] )
			? $namespaces[ 'wp' ]
			: '';
	}
}