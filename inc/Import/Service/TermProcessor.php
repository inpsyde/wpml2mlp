<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Iterator;

class TermProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\TermIterator
	 */
	private $iterator;

	/**
	 * @var TermImporterInterface
	 */
	private $importer;

	/**
	 * @param Iterator\TermIterator $iterator
	 * @param TermImporterInterface $importer
	 */
	public function __construct(
		Iterator\TermIterator $iterator,
		TermImporterInterface $importer
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
	}

	/**
	 * Import terms and fires the action `w2m_import_terms_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		while ( $this->iterator->valid() ) {
			$import_term = $this->iterator->current();
			if ( $import_term ) {
				$this->importer->import_term( $import_term );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_terms_done' );
	}

}