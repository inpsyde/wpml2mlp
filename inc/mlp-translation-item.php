<?php

class MLP_Translation_Item {

	private $source;

	private $destination;

	private $source_language;

	private $destination_language;

	public function __construct( $source, $destination, $source_language, $destination_language ) {

		$this->source               = $source;
		$this->destination          = $destination;
		$this->source_language      = $source_language;
		$this->destination_language = $destination_language;
	}

	public function getSource() {

		return $this->source;
	}

	public function  getDestination() {

		return $this->destination;
	}

	public function getSourceLanguage() {

		return $this->source_language;
	}

	public function getDestinationLanguage() {

		return $this->destination_language;
	}

	public function isValid() {

		return ! empty( $this->source ) && ! empty( $this->destination ) && ! empty( $this->source_language ) && ! empty( $this->destination_language );
	}
}