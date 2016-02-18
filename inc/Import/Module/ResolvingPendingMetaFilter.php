<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Data;

/**
 * Class ResolvingPendingMetaFilter
 *
 * Reads postponed meta filter from the list and
 * tries to re-apply them at the end of the import-process.
 *
 * @package W2M\Import\Module
 */
class ResolvingPendingMetaFilter {

	/**
	 * @var Data\ImportListeningMetaFilterList
	 */
	private $meta_list;

	/**
	 * @param Data\ImportListeningMetaFilterList $meta_filter_list
	 */
	public function __construct( Data\ImportListeningMetaFilterList $meta_filter_list ) {

		$this->meta_list = $meta_filter_list;
	}

	/**
	 * @wp-hook w2m_import_process_done
	 */
	public function resolve_pending_meta_filter() {

		// Todo: Iterate over postponed meta filters and re-apply them
	}
}