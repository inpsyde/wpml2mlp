<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Parser;

use
	W2M\Import\Type,
	SimpleXMLElement;

/**
 * Interface CommentParserInterface
 *
 * @package W2M\Import\Service
 */
interface CommentParserInterface {

	/**
	 * @param SimpleXMLElement $document
	 *
	 * @return Type\ImportCommentInterface|NULL
	 */
	public function parse_comment( SimpleXMLElement $document );
}