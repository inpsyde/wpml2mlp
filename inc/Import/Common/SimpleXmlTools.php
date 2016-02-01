<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Common;

use
	SimpleXMLElement;

class SimpleXmlTools {

	/**
	 * @param SimpleXMLElement $document
	 * @param $namespace
	 *
	 * @return string|null
	 */
	public function get_doc_namespace( SimpleXMLElement $document, $namespace ) {

		$doc_ns = $document->getDocNamespaces( TRUE );

		return isset( $doc_ns[ $namespace ] )
			? $doc_ns[ $namespace ]
			: NULL;
	}
}