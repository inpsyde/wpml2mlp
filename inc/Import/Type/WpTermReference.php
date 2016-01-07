<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Class WpTermReference
 *
 * @package W2M\Import\Type
 */
class WpTermReference implements TermReferenceInterface {

	private $origin_id = 0;

	private $taxonomy = '';

	public function __construct( $origin_id, $taxonomy ) {

		$this->origin_id = (int) $origin_id;
		$this->taxonomy  = (string) $taxonomy;
	}

	/**
	 * @return int
	 */
	public function origin_id() {

		return $this->origin_id;
	}

	/**
	 * @return string
	 */
	public function taxonomy() {

		return $this->taxonomy;
	}

}