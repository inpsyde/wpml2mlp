<?php

/**
 * Class Wpml2mlp_Translation_Item
 */
class Wpml2mlp_Translation_Item {

	/**
	 * @var string
	 */
	public $source;

	/**
	 * @var string
	 */
	public $target;

	/**
	 * @var int
	 */
	public $original_id;

	/**
	 * @var int
	 */
	public $post_id;

	/**
	 * Constructs new Wpml2mlp_Translation_Item
	 *
	 * @param $source
	 * @param $destination
	 * @param $original_id
	 * @param $post_id
	 */
	public function __construct( $source, $destination, $original_id, $post_id ) {

		$this->source      = $source;
		$this->target      = $destination;
		$this->original_id = $original_id;
		$this->post_id     = $post_id;
	}

	/**
	 * Checks is the Translation item valid.
	 *
	 * @return bool
	 */
	public function is_valid() {

		return ! empty( $this->source ) && $this->original_id > 0 && $this->post_id > 0;
	}
}