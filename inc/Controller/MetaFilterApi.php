<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import\Data;

/**
 * Class MetaFilterApi
 *
 * Applies w2m_import_meta_filter to the meta filter list to allow
 * third party code to adapt the filters.
 *
 * @package W2M\Controller
 */
class MetaFilterApi {

	/**
	 * @var Data\MetaFilterListInterface
	 */
	private $filter_list;

	private $postponed_filter_list;

	/**
	 * @param Data\MetaFilterListInterface $filter_list
	 * @param $postponed_filter_list (Todo: specify implement this)
	 */
	public function __construct(
		Data\MetaFilterListInterface $filter_list,
		$postponed_filter_list = NULL
	) {

		$this->filter_list           = $filter_list;
		$this->postponed_filter_list = $postponed_filter_list;
	}

	/**
	 * Applies w2m_import_meta_filter to the meta filter list to allow
	 * third party code to adapt the filters.
	 */
	public function register_filter() {

		/**
		 * Use this hook to register filters to specific meta keys for specific
		 * element types
		 *
		 * @see \W2M\Import\Filter\ValueFilterableInterface
		 *
		 * @param Data\MetaFilterListInterface
		 */
		do_action( 'w2m_import_meta_filter', $this->filter_list );
	}
}