<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Filter,
	W2M\Import\Iterator;

/**
 * Class UserProcessor
 *
 * @package W2M\Import\Service
 */
class UserProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\UserIterator
	 */
	private $iterator;

	/**
	 * @var Importer\UserImporterInterface
	 */
	private $importer;

	/**
	 * @var Filter\UserImportFilterInterface
	 */
	private $filter;

	/**
	 * @param Iterator\UserIterator $iterator
	 * @param Importer\UserImporterInterface $importer
	 * @param Filter\UserImportFilterInterface $filter (Optional)
	 */
	public function __construct(
		Iterator\UserIterator $iterator,
		Importer\UserImporterInterface $importer,
		Filter\UserImportFilterInterface $filter = NULL
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
		$this->filter   = $filter
			? $filter
			: new Filter\UserPassThroughFilter;
	}

	/**
	 * Import users and fires the action `w2m_import_users_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		do_action( 'w2m_import_users_start' );

		while ( $this->iterator->valid() ) {
			$import_user = $this->iterator->current();
			if ( $import_user && $this->filter->user_to_import( $import_user ) ) {
				$this->importer->import_user( $import_user );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_users_done' );
	}
}