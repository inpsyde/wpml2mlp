<?php

/**
 * Class Wpml2mlp_Language_Holder
 */
class Wpml2mlp_Language_Holder {

	/**
	 * @var array
	 */
	private $mapper;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$this->mapper = array();
	}

	/**
	 * Initializes new language in language holder.
	 *
	 * @param Wpml2mlp_Translation_Item $translation_item
	 * @param                           $source_lang
	 * @param                           $destination_lang
	 */
	public function set_item( Wpml2mlp_Translation_Item &$translation_item, $source_lang, $destination_lang ) {

		if ( $translation_item == NULL
			|| ! $translation_item->is_valid()
			|| empty( $source_lang )
			|| empty( $destination_lang )
		) {
			return;
		}

		$this->check_language( $source_lang, $destination_lang );
		$translations = $this->mapper[ $destination_lang ];
		$translations->push( $translation_item );
		$this->mapper[ $destination_lang ] = $translations;
	}

	/**
	 * Gets all languages mappings.
	 *
	 * @return array
	 */
	public function get_all_items() {

		return array_values( $this->mapper );
	}

	/**
	 * Checks if language exists in language mapper, if not creates new Wpml2mlp_Translations object from given
	 * lanugage and push it to the mappings.
	 *
	 * @param $source_lang
	 * @param $destination_lang
	 *
	 * @return Wpml2mlp_Translations instance from mapper.
	 */
	private function check_language( $source_lang, $destination_lang ) {

		if ( ! array_key_exists( $destination_lang, $this->mapper ) ) {
			$this->mapper[ $destination_lang ] = new Wpml2mlp_Translations( $source_lang, $destination_lang );
		}

		return $this->mapper[ $destination_lang ];
	}
}