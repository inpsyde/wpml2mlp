<?php # -*- coding: utf-8 -*-

namespace W2M\Import\ObjectCreation;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * @deprecated Use Service\*ParserInterface instead
 */
interface WpImportObjectBuilderInterface {

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportTermInterface
	 */
	public function build_import_term( SimpleXMLElement $element );

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportPostInterface
	 */
	public function build_import_post( SimpleXMLElement $element );

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportUserInterface
	 */
	public function build_import_user( SimpleXMLElement $element );
}