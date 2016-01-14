<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	W2M\Import\Service,
	W2M\Import\Type,
	Iterator;

class CommentIterator implements Iterator {

	/**
	 * @var SimpleXmlItemWrapper
	 */
	private $iterator;

	private $parser;

	/**
	 * @param SimpleXmlItemWrapper $iterator
	 * @param Service\CommentParserInterface $parser
	 */
	public function __construct(
		SimpleXmlItemWrapper $iterator,
		Service\CommentParserInterface $parser
	) {

		$this->iterator = $iterator;
		$this->parser   = $parser;
	}

	/**
	 * Return the current ImportComment
	 *
	 * @return Type\ImportCommentInterface
	 */
	public function current() {

		$comment_document = $this->iterator->current();
		$import_comment = NULL;
		if ( is_a( $comment_document, 'SimpleXMLElement' ) ) {
			$import_comment = $this->parser->parse_comment( $comment_document );
		}

		return $import_comment;
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