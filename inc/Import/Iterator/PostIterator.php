<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	W2M\Import\Type,
	W2M\Import\Service,
	Iterator;

class PostIterator implements Iterator {

	/**
	 * @var SimpleXmlItemWrapper
	 */
	private $iterator;

	/**
	 * @var Service\PostParserInterface
	 */
	private $parser;

	/**
	 * @param Service\PostParserInterface $parser
	 * @param SimpleXmlItemWrapper $iterator
	 */
	public function __construct(
		Service\PostParserInterface $parser,
		SimpleXmlItemWrapper $iterator
	) {

		$this->parser   = $parser;
		$this->iterator = $iterator;
	}

	/**
	 * Return the current element
	 *
	 * @return Type\ImportPostInterface|NULL
	 */
	public function current() {

		$post_document = $this->iterator->current();
		$import_post   = NULL;
		if ( is_a( $post_document, 'SimpleXMLElement' ) )
			$import_post = $this->parser->parse_post( $post_document );

		return $import_post;
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