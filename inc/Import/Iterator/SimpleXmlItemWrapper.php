<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Iterator;

use
	W2M\Import\Common,
	SimpleXMLElement,
	WP_Error,
	Iterator;

/**
 * Class SimpleXmlItemWrapper
 *
 * @package W2M\Import\Iterator
 */
class SimpleXmlItemWrapper implements Iterator {

	/**
	 * @var Iterator
	 */
	private $iterator;

	/**
	 * @var string
	 */
	private $root_el;

	/**
	 * @var string
	 */
	private $simple_xml_class;

	private $wp_factory;

	/**
	 * @param Iterator $iterator
	 * @param string $root_el
	 * @param array $simple_xml_config [
	 *      string $class
	 * ]
	 * @param Common\WpFactoryInterface $wp_factory (Optional)
	 */
	public function __construct(
		Iterator $iterator,
		$root_el = 'root',
		Array $simple_xml_config = array(),
		Common\WpFactoryInterface $wp_factory = NULL
	) {

		$this->iterator         = $iterator;
		$this->root_el          = (string) $root_el;
		$this->simple_xml_class = isset( $simple_xml_config[ 'class' ] )
			? (string) $simple_xml_config[ 'class' ]
			: 'SimpleXMLElement';

		//Todo: Handle $simple_xml_config[ 'options' ];

		$this->wp_factory = $wp_factory
			? $wp_factory
			: new Common\WpFactory;
	}

	/**
	 * Return the current element
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return SimpleXMLElement|NULL
	 */
	public function current() {

		$xml = sprintf(
			'<%1$s>%2$s</%1$s>',
			$this->root_el,
			$this->iterator->current()
		);

		$previous_error_handling = libxml_use_internal_errors( TRUE );
		libxml_clear_errors();
		$document = simplexml_load_string( $xml, $this->simple_xml_class );
		if ( ! is_a( $document, $this->simple_xml_class ) ) {
			$error = $this->wp_factory->wp_error( 'xml', "Invalid XML" );
			$error->add_data(
				[
					'data' => [
						'xml_string' => $xml,
						'xml_errors' => libxml_get_errors()
					]
				],
				'xml'
			);
			libxml_clear_errors();
			$this->propagate_invalid_xml_error( $error );
		}
		// set back to previous state if this was not internal
		if ( ! $previous_error_handling )
			libxml_use_internal_errors( $previous_error_handling );
		return $document;
	}

	/**
	 * @param WP_Error $error
	 */
	private function propagate_invalid_xml_error( WP_Error $error ) {

		/**
		 * @param WP_Error $error {
		 *      error_code: xml
		 *      error_data: [
		 *              "data" => [ string 'xml_string', array 'xml_errors' ]
		 *      ]
		 * }
		 */
		do_action( 'w2m_import_xml_parser_error', $error );
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