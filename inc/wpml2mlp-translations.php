<?php

class MLP_Translations {

	public $source_language;

	public $destination_language;

	public $data;

	public function __construct( $source_lang, $destination_lang ) {

		$this->source_language      = $source_lang;
		$this->destination_language = $destination_lang;
		$this->data                 = array();
	}

	public function push( MLP_Translation_Item &$translation_Item ) {

		if ( $translation_Item->is_valid() ) {
			array_push( $this->data, $translation_Item );
		}
	}
}