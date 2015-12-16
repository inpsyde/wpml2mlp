<?php

/**
 * Class Wpml2mlp_Translations_Builder
 */
class Wpml2mlp_Translations_Builder {

	/**
	 *
	 * @var string
	 */
	private $default_language;

	/**
	 * Constructs the WPML2MLP_Translations_Builder
	 *
	 */
	public function __construct( $default_language ) {

		$this->default_language = $default_language;
	}

	/**
	 * Builds translations from given post.
	 *
	 * @param $post
	 * @param $mlp_post_id
	 *
	 * @return array
	 */
	public function build_translation_item( $post, $mlp_post_id ) {

		$ret = array();

		$source_id = Wpml2mlp_Helper::get_default_post_ID( $post );

		$source_post = get_post( $source_id );

		if ( $source_post == NULL && $source_post == $mlp_post_id ) {
			return FALSE;
		}

		// put translations here for current post
		array_push(
			$ret,
			new Wpml2mlp_Translation_Item( $source_post->post_title, $post->post_title, $source_id, $mlp_post_id, $source_post->post_type )
		);
		array_push(
			$ret,
			new Wpml2mlp_Translation_Item( $source_post->post_content, $post->post_content, $source_id, $mlp_post_id )
		);

		return $ret;
	}
}