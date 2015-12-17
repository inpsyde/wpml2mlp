<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportPost
 */
interface ImportPost extends ImportElementInterface {

	/**
	 * @return string
	 */
	public function title();

	/**
	 * @return string
	 */
	public function guid();

	/**
	 * @return \DateTime
	 */
	public function date();

	/**
	 * @return string
	 */
	public function commment_status();

	/**
	 * @return string
	 */
	public function ping_status();

	/**
	 * @return string
	 */
	public function type();

	/**
	 * @return bool
	 */
	public function is_sticky();

	/**
	 * @return string
	 */
	public function origin_link();

	/**
	 * @return string
	 */
	public function excerpt();

	/**
	 * @return string
	 */
	public function content();

	/**
	 * @return string
	 */
	public function name();

	/**
	 * @return int
	 */
	public function origin_post_parent_id();

	/**
	 * @return int
	 */
	public function menu_order();

	/**
	 * @return string
	 */
	public function password();

	/**
	 * @return array
	 */
	public function terms();

	/**
	 * @return array
	 */
	public function meta();

	/**
	 * @return array
	 */
	public function locale_relations();
}