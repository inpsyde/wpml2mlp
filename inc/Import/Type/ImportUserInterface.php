<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

/**
 * Interface ImportUserInterface
 *
 * @package W2M\Import\Type
 */
interface ImportUserInterface extends ImportElementInterface {

	/**
	 * @return string
	 */
	public function login();

	/**
	 * @return string
	 */
	public function email();

	/**
	 * @return string
	 */
	public function first_name();

	/**
	 * @return string
	 */
	public function last_name();

	/**
	 * @return string
	 */
	public function display_name();

	/**
	 * @return string
	 */
	public function role();

}