<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface MetaRecordIndexInterface
 *
 * Describes a »pointer« to a concrete ImportMeta record
 *
 * @package W2M\Import\Type
 */
interface MetaRecordIndexInterface {

	/**
	 * The type the meta data belongs to
	 *
	 * @return string
	 */
	public function type();

	/**
	 * @return int
	 */
	public function object_id();

	/**
	 * @return string
	 */
	public function key();

	/**
	 * @return int
	 */
	public function index();
}