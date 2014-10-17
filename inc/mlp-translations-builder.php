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

	public function build_translation_item( $post ) {

		$language = WPML2MLP_Helper::get_language_info( $post->ID );
		$locale   = $language[ 'locale' ];

		$dest_lang = WPML2MLP_Helper::get_short_language( $locale ); // TODO: cache this

		if ( $dest_lang == $this->default_language ) {
			return FALSE; // it is default language, we don't need export for this
		}

		$ret = array();

		$source_id = (int) icl_object_id( $post->ID, $post->post_type, TRUE );

		$source_post = get_post( $source_id ); // TODO: cache this

		if ( $source_post == NULL ) {
			return FALSE;
		}

		// put translations here for current post
		array_push(
			$ret, $this->construct_translation_item( $source_post->post_title, $post->post_title, $dest_lang )
		);
		array_push(
			$ret, $this->construct_translation_item( $source_post->post_content, $post->post_content, $dest_lang )
		);

		return $ret;
	}

	private function construct_translation_item( $source_val, $dest_val, $dest_lang ) {

		return new MLP_Translation_Item( $source_val, $dest_val, $this->default_language, $dest_lang );
	}
}