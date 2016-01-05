<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * Interface PostParserInterface
 *
 * Prases a SimpleXMLElement and creates a ImportPostInterface instance
 *
 * @package W2M\Import\Service
 */
interface PostParserInterface {

	/**
	 * @param SimpleXMLElement $post
	 *
	 * @return Type\ImportPostInterface
	 */
	public function parse_post( SimpleXMLElement $post );
}