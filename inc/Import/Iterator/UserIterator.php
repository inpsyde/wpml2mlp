<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	W2M\Import\Type,
	W2M\Import\Service,
	Iterator;

/**
 * Class UserIterator
 *
 * @package W2M\Import\Iterator
 */
class UserIterator implements Iterator {

	/**
	 * @var SimpleXmlItemWrapper
	 */
	private $iterator;

	/**
	 * @var Service\Parser\UserParserInterface
	 */
	private $parser;

	/**
	 * @param SimpleXmlItemWrapper $iterator
	 * @param Service\Parser\UserParserInterface $parser
	 */
	public function __construct(
		SimpleXmlItemWrapper $iterator,
		Service\Parser\UserParserInterface $parser
	) {

		$this->iterator = $iterator;
		$this->parser   = $parser;
	}

	/**
	 * Return the current ImportComment
	 *
	 * @return Type\ImportUserInterface
	 */
	public function current() {

		$user_document = $this->iterator->current();
		$import_user   = NULL;
		if ( is_a( $user_document, 'SimpleXMLElement' ) ) {
			$import_user = $this->parser->parse_user( $user_document );
		}

		return $import_user;
	}

	/**
	 * Move forward to next element
	 *
	 * @return void
	 */
	public function next() {

		$this->iterator->next();
	}

	/**
	 * Return the key of the current element
	 *
	 * @return mixed
	 */
	public function key() {

		return $this->iterator->key();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean
	 */
	public function valid() {

		return $this->iterator->valid();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @return void
	 */
	public function rewind() {

		$this->iterator->rewind();
	}
}