<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Filter,
	W2M\Import\Iterator;

/**
 * Class TermProcessor
 *
 * @package W2M\Import\Service
 */
class TermProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\TermIterator
	 */
	private $iterator;

	/**
	 * @var Importer\TermImporterInterface
	 */
	private $importer;

	/**
	 * @var Filter\TermImportFilterInterface
	 */
	private $filter;

	/**
	 * @param Iterator\TermIterator $iterator
	 * @param Importer\TermImporterInterface $importer
	 * @param Filter\TermImportFilterInterface $filter (Optional)
	 */
	public function __construct(
		Iterator\TermIterator $iterator,
		Importer\TermImporterInterface $importer,
		Filter\TermImportFilterInterface $filter = NULL
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
		$this->filter   = $filter
			? $filter
			: new Filter\TermPassThroughFilter;
	}

	/**
	 * Import terms and fires the action `w2m_import_terms_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		do_action( 'w2m_import_terms_start' );

		while ( $this->iterator->valid() ) {
			$import_term = $this->iterator->current();
			if ( $import_term && $this->filter->term_to_import( $import_term ) ) {
				$this->importer->import_term( $import_term );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_terms_done' );
	}

}