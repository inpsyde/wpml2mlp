<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

/**
 * Interface ElementProcessorInterface
 *
 * Simple mediator between Iterators and Importers
 *
 * @package W2M\Import\Service
 */
interface ElementProcessorInterface {

	/**
	 * Should fire an action when finished: `w2m_import_{type}s_done`
	 *
	 * @return void
	 */
	public function process_elements();
}