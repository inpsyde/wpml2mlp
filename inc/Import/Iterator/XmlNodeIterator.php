<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	XMLReader,
	Iterator;

/**
 * Class XmlNodeIterator
 *
 * Iterates over all given nodes with the given
 * node name. current() returns the nodes inner XML
 *
 * @package W2M\Import\Xml
 */
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
	public function __construct( $uri, $node_name = '', XMLReader $reader = NULL ) {

		$this->uri = (string) $uri;
		$this->node_name = (string) $node_name;
		$this->reader = $reader
			? $reader
			: new XMLReader( $reader );

		if ( ! $reader )
			$this->reader->open( $uri );
	}

	/**
	 * @return void
	 */
	public function next() {

		/**
		 * this is a bit tricky. XMLReader does not
		 * separate next() and read() in fact read()
		 * moves the cursor forward.
		 *
		 * Todo: verify this!
		 */
	}

	/**
	 * @return string
	 */
	public function current() {

		$xml = $this->reader->readInnerXml();
		return ! empty( $this->node_name )
			? sprintf( '<%1$s>%2$s</%1$s>', $this->node_name, $xml )
			: $xml;
	}

	/**
	 * @return null
	 */
	public function key() {

		return NULL;
	}

	/**
	 * Move the cursor to the specified element
	 *
	 * @return bool
	 */
	public function valid() {

		while ( $this->reader->read() ) {
			if (
				$this->node_name === $this->reader->name
			 && XML_ELEMENT_NODE === $this->reader->nodeType
			)
				return TRUE;
		}

		return FALSE;
	}

	/**
	 * closes the reader and re-open the URI
	 *
	 * Todo: consider if it is necessary or even reliable
	 */
	public function rewind() {

		$this->reader->close();
		$this->reader->open( $this->uri );
	}
}