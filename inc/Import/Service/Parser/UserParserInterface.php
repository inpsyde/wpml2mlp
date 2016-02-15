<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Parser;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * Interface UserParserInterface
 *
 * @package W2M\Import\Service\Parser
 */
interface UserParserInterface {

	/**
	 * @param SimpleXMLElement $element
	 *
	 * @return Type\ImportUserInterface|NULL
	 */
	public function parse_user( SimpleXMLElement $element );
}