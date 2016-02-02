<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Iterator;

class UserProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\UserIterator
	 */
	private $iterator;

	/**
	 * @var UserImporterInterface
	 */
	private $importer;

	/**
	 * @param Iterator\UserIterator $iterator
	 * @param UserImporterInterface $importer
	 */
	public function __construct(
		Iterator\UserIterator $iterator,
		UserImporterInterface $importer
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
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
			if ( $import_user ) {
				$this->importer->import_user( $import_user );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_users_done' );
	}
}