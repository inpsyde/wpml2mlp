<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

interface LocaleRelationInterface {

	/**
	 * @return int
	 */
	public function origin_id();

	/**
	 * @return string
	 */
	public function locale();
}