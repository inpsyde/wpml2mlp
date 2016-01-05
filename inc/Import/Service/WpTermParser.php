<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	SimpleXMLElement;

class WpTermParser implements TermParserInterface {

	/**
	 * @param SimpleXMLElement $term
	 *
	 * @return Type\ImportTermInterface
	 */
	public function parse_term( SimpleXMLElement $term ) {


	}

}