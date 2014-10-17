<?php

class MLP_Language_Holder {

	private $mapper;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		$this->mapper = array();
	}

	public function setItem( MLP_Translation_Item &$translationItem ) {

		if ( $translationItem == NULL || ! $translationItem->isValid() ) {
			return;
		}

		$lng = $translationItem->getDestinationLanguage();
		$this->checkLanguage( $lng );

		array_push( $this->mapper[ $lng ], $translationItem );
	}

	public function getAllItems() {

		return $this->mapper;
	}

	private function checkLanguage( $language ) {

		if ( ! array_key_exists( $language, $this->mapper ) ) {
			$this->mapper[ $language ] = array();
		}
	}
}