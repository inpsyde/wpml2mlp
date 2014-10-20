<?php

class WPML2MLP_Language_Holder {

	private $mapper;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$this->mapper = array();
	}

	public function set_item( WPML2MLP_Translation_Item &$translation_item, $source_lang, $destination_lang ) {

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

	public function get_all_items() {

		return array_values( $this->mapper );
	}

	private function check_language( $source_lang, $destination_lang ) {

		if ( ! array_key_exists( $destination_lang, $this->mapper ) ) {
			$this->mapper[ $destination_lang ] = new WPML2MLP_Translations( $source_lang, $destination_lang );
		}

		return $this->mapper[ $destination_lang ];
	}
}