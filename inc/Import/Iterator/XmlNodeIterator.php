<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Xml;

use
	XMLReader,
	Iterator;

class XmlNodeIterator implements Iterator {

	/**
	 * @var XMLReader
	 */
	private $reader;

	/**
	 * @var string
	 */
	private $node_name;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * @param string $uri Filename or URI to an XML resource
	 * @param string $node_name Name of the element to iterate over
	 * @param XMLReader $reader Optional
	 */
	public function __construct( $uri, $node_name, XMLReader $reader = NULL ) {

		$this->uri = (string) $uri;
		$this->node_name = (string) $node_name;
		$this->reader = $reader
			? $reader
			: new XMLReader( $reader );

		if ( ! $reader )
			$this->reader->open( $uri );
	}

	/**
	 * @return string
	 */
	public function current() {
		// TODO: Implement current() method.
	}

	/**
	 * @return bool
	 */
	public function next() {

		return $this->reader->next( $this->node_name );
	}

	/**
	 * @return null
	 */
	public function key() {

		return NULL;
	}

	/**
	 * @return bool
	 */
	public function valid() {

		return $this->node_name === $this->reader->name;
	}

	/**
	 * closes the reader and re-open the URI
	 */
	public function rewind() {

		$this->reader->close();
		$this->reader->open( $this->uri );
	}

}