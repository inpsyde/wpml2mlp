<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import\Data,
	W2M\Import\Module;

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

	/**
	 * @var Data\ImportListeningMetaFilterList
	 */
	private $postponed_filter_list;

	/**
	 * @var Module\PostponedMetaFilterResolver
	 */
	private $resolver;

	/**
	 * @param Data\MetaFilterListInterface $filter_list
	 * @param Data\ImportListeningMetaFilterList $postponed_filter_list
	 * @param Module\PostponedMetaFilterResolver $resolver
	 */
	public function __construct(
		Data\MetaFilterListInterface $filter_list,
		Data\ImportListeningMetaFilterList $postponed_filter_list,
		Module\PostponedMetaFilterResolver $resolver
	) {

		$this->filter_list           = $filter_list;
		$this->postponed_filter_list = $postponed_filter_list;
		$this->resolver              = $resolver;
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

		add_action( 'w2m_import_meta_not_filterable', [ $this->postponed_filter_list, 'record_meta_filter' ], 10, 3 );
		add_action( 'w2m_import_process_done', [ $this->resolver, 'resolve_pending_meta_filter' ] );
	}
}