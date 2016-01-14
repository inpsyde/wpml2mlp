<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	W2m\Import\Type,
	W2m\Import\Service,
	Iterator;

class TermIterator implements Iterator {

	/**
	 * @var SimpleXmlItemWrapper
	 */
	private $iterator;

	/**
	 * @var Service\TermParserInterface
	 */
	private $parser;

	/**
	 * @param SimpleXmlItemWrapper $iterator
	 * @param Service\TermParserInterface $parser
	 */
	public function __construct(
		SimpleXmlItemWrapper $iterator,
		Service\TermParserInterface $parser
	) {

		$this->iterator = $iterator;
		$this->parser   = $parser;
	}

	/**
	 * Return the current element
	 *
	 * @return Type\ImportTermInterface|NULL
	 */
	public function current() {

		$term_document = $this->iterator->current();
		$import_term   = NULL;
		if ( is_a( $term_document, 'SimpleXMLElement' ) ) {
			$import_term = $this->parser->parse_term( $term_document );
		}

		return $import_term;
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

		$this->iterator->valid();
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