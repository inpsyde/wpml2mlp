<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	DateTime;

/**
 * Interface ImportCommentInterface
 *
 * @package W2M\Import\Type
 */
interface ImportCommentInterface extends ImportElementInterface {

	/**
	 * @return int
	 */
	public function origin_post_id();

	/**
	 * @return string
	 */
	public function author_name();

	/**
	 * @return string
	 */
	public function author_email();

	/**
	 * @return string
	 */
	public function author_url();

	/**
	 * @return string
	 */
	public function author_ip();

	/**
	 * @return DateTime
	 */
	public function date();

	/**
	 * @return string
	 */
	public function content();

	/**
	 * @return int
	 */
	public function karma();

	/**
	 * @return string
	 */
	public function approved();

	/**
	 * @return string
	 */
	public function agent();

	/**
	 * @return string
	 */
	public function type();

	/**
	 * @return int
	 */
	public function origin_user_id();

	/**
	 * @return int
	 */
	public function origin_parent_comment_id();

	/**
	 * @return ImportMetaInterface[]
	 */
	public function meta();
}