<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	Iterator;

class SimpleXmlItemParser implements Iterator {

	/**
	 * @var Iterator
	 */
	private $iterator;

	/**
	 * @var array
	 */
	private $namespaces = array();

	/**
	 * @var string
	 */
	private $root_el;

	private $simple_xml_class;

	/**
	 * @param Iterator $iterator
	 * @param array $namespaces
	 * @param string $root_el
	 * @param array $simple_xml_config [
	 *      string $class
	 * ]
	 *
	 */
	public function __construct(
		Iterator $iterator,
		Array $namespaces = array(),
		$root_el = 'root',
		Array $simple_xml_config = array()
	) {

		$this->iterator         = $iterator;
		$this->namespaces       = $namespaces;
		$this->root_el          = (string) $root_el;
		$this->simple_xml_class = isset( $simple_xml_config[ 'class' ] )
			? (string) $simple_xml_config[ 'class' ]
			: 'SimpleXMLElement';

		//Todo: Handle $simple_xml_config[ 'options' ];
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {

		$namespaces = array();
		foreach ( $this->namespaces as $ns => $uri ) {
			$namespaces[] = "xmlns:{$ns}='{$uri}'";
		}

		$xml = sprintf(
			'<%1$s %2$s>%3$s</%1$s>',
			$this->root_el,
			implode( ' ', $namespaces ),
			$this->iterator->current()
		);

		return simplexml_load_string( $xml, $this->simple_xml_class );
	}

	/**
	 * Move forward to next element
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {

		$this->iterator->next();
	}

	/**
	 * Return the key of the current element
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
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
	 */
	public function valid() {

		return $this->iterator->valid();
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {

		$this->iterator->rewind();
	}
}