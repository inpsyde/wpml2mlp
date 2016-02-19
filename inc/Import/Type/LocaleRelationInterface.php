<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface LocaleRelationInterface
 *
 * Describes a relation of an element to a remote element
 * by the locale and origin_id of the remote element
 *
 * @package W2M\Import\Type
 */
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