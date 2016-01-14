<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Iterator;

class CommentProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\CommentIterator
	 */
	private $iterator;

	/**
	 * @var CommentImporterInterface
	 */
	private $importer;

	/**
	 * @param Iterator\CommentIterator $iterator
	 * @param CommentImporterInterface $importer
	 */
	public function __construct(
		Iterator\CommentIterator $iterator,
		CommentImporterInterface $importer
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
	}

	/**
	 * Import comments and fires the action `w2m_import_comments_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		while ( $this->iterator->valid() ) {
			$import_comment = $this->iterator->current();
			if ( $import_comment ) {
				$this->importer->import_comment( $import_comment );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_comments_done' );
	}
}