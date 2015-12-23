<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	Iterator,
	W2M\Import\Type;

/**
 * Class TermItemIterator
 *
 * This decorates another Iterator that should return
 * SimpleXMLElements on the current() method.
 *
 * @package W2M\Import\Iterator
 */
class TermItemIterator implements Iterator {

	/**
	 * @var Iterator
	 */
	private $iterator;

	/**
	 * @param Iterator $iterator
	 */
	public function __construct( Iterator $iterator ) {

		$this->iterator = $iterator;
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return Type\ImportTermInterface
	 * @since 5.0.0
	 */
	public function current() {


	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next() {

		$this->iterator->next();
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key() {

		return $this->iterator->key();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid() {

		return $this->iterator->valid();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind() {

		$this->iterator->rewind();
	}
}