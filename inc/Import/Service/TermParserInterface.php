<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * Interface TermParserInterface
 *
 * Parses a SimpleXMLElement and creates an ImportTermInterface instance
 *
 * @package W2M\Import\Service
 */
interface TermParserInterface {

	/**
	 * @param SimpleXMLElement $term
	 *
	 * @return Type\ImportTermInterface
	 */
	public function parse_term( SimpleXMLElement $term );
}