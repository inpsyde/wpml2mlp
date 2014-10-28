<?php

/**
 * Class Wpml2mlp_Translations
 */
class Wpml2mlp_Translations {

	/**
	 * @var string
	 */
	public $source_language;

	/**
	 * @var string
	 */
	public $destination_language;

	/**
	 * @var array
	 */
	public $data;

	/**
	 * Constructs new Wpml2mlp_Translations object
	 *
	 * @param $source_lang
	 * @param $destination_lang
	 */
	public function __construct( $source_lang, $destination_lang ) {

		$this->source_language      = $source_lang;
		$this->destination_language = $destination_lang;
		$this->data                 = array();
	}

	/**
	 * PUshes new Translation item to translations
	 *
	 * @param WPML2MLP_Translation_Item $translation_Item
	 */
	public function push( WPML2MLP_Translation_Item &$translation_Item ) {

		if ( $translation_Item->is_valid() ) {
			array_push( $this->data, $translation_Item );
		}
	}
}