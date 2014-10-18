<?php

class MLP_Translations_Builder {

	/**
	 *
	 * @var string
	 */
	private $default_language;

	/**
	 * Constructs the MLP_Translations_Builder
	 *
	 */
	public function __construct( $default_language ) {

		$this->default_language = $default_language;
	}

	public function build_translation_item( $post, $mlp_post_id ) {

		$ret = array();

		$source_id = WPML2MLP_Helper::get_default_post_ID($post);

		$source_post = get_post( $source_id ); // TODO: cache this

		if ( $source_post == NULL ) {
			return FALSE;
		}

		// put translations here for current post
		array_push(
			$ret,
			$this->construct_translation_item( $source_post->post_title, $post->post_title, $mlp_post_id, $source_id )
		);
		array_push(
			$ret, $this->construct_translation_item(
				$source_post->post_content, $post->post_content, $mlp_post_id, $source_id
			)
		);

		return $ret;
	}

	private function construct_translation_item( $source_val, $dest_val, $post_id, $source_id ) {

		return new MLP_Translation_Item( $source_val, $dest_val, $source_id, $post_id );
	}
}