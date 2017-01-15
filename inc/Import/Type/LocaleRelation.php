<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Class LocaleRelation
 *
 * @package W2M\Import\Type
 */
class LocaleRelation implements LocaleRelationInterface {

	/**
	 * @var string
	 */
	private $locale = '';

	/**
	 * @var int
	 */
	private $origin_id = 0;

	/**
	 * @param string $locale
	 * @param int $origin_id
	 */
	public function __construct( $locale, $origin_id ) {

		$this->locale    = (string) $locale;
		$this->origin_id = (int) $origin_id;
	}

	/**
	 * @return int
	 */
	public function origin_id() {

		return $this->origin_id;
	}

	/**
	 * @return string
	 */
	public function locale() {

		return $this->locale;
	}

}