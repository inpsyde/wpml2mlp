<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportPostInterface
 *
 * @package W2M\Import\Type
 */
interface ImportPostInterface extends ImportElementInterface {

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
	public function comment_status();

	/**
	 * @return string
	 */
	public function ping_status();

	/**
	 * @return string
	 */
	public function origin_author_id();

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
	 * @return string
	 */
	public function status();

	/**
	 * @return int
	 */
	public function origin_parent_post_id();

	/**
	 * @return int
	 */
	public function menu_order();

	/**
	 * @return string
	 */
	public function password();

	/**
	 * @return TermReferenceInterface[]
	 */
	public function terms();

	/**
	 * @return ImportMetaInterface[]
	 */
	public function meta();

	/**
	 * @return LocaleRelationInterface[]
	 */
	public function locale_relations();


	/**
	 * @return string
	 */
	public function origin_attachment_url();
}