<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportTermInterface
 *
 * @package W2M\Import\Type
 */
interface ImportTermInterface extends ImportElementInterface {

	/**
	 * @return string
	 */
	public function taxonomy();

	/**
	 * @return string
	 */
	public function name();

	/**
	 * @return string
	 */
	public function slug();

	/**
	 * @return string
	 */
	public function description();

	/**
	 * @return int
	 */
	public function origin_parent_term_id();

	/**
	 * @return LocaleRelationInterface[]
	 */
	public function locale_relations();
}