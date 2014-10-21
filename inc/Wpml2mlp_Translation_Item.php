<?php

class Wpml2mlp_Translation_Item {

	public $source;

	public $target;

	public $original_id;

	public $post_id;

	public function __construct( $source, $destination, $original_id, $post_id ) {

		$this->source      = $source;
		$this->target      = $destination;
		$this->original_id = $original_id;
		$this->post_id     = $post_id;
	}

	public function is_valid() {

		return ! empty( $this->source ) && $this->original_id > 0 && $this->post_id > 0;
	}
}