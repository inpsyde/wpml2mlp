<?php

/**
 * Class Wpml2mlp_Translation_Item
 */
class Wpml2mlp_Translation_Item {

	/**
	 * @var string
	 */
	public $source_item;

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
	public function __construct( $source, $destination, $original_id, $post_id, $post_type ) {

		#TODO build a better Object
		#$this->source_item = $source;

		#$this->source      = $this->set_source();
		#$this->targets     = $this->set_translations();

		$this->source      = $source;
		$this->target      = $destination;
		$this->original_id = $original_id;
		$this->post_id     = $post_id;
		$this->post_type   = $post_type;


	}

	/**
	 * Checks is the Translation item valid.
	 *
	 * @return bool
	 */
	public function is_valid() {

		return ! empty( $this->source ) && $this->original_id > 0 && $this->post_id > 0;
	}

	/**
	 * grab the source item.
	 *
	 * @return object
	 */
	public function set_source() {

		$source = $this->source_item;

		return $source;
	}

	/**
	 * grab the translations of an item.
	 *
	 * @return object
	 */
	public function set_translations() {

		$translations = Wpml2mlp_Helper::get_language_info( $this->source_item->ID );

		return $translations;

	}

}
