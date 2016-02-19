<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Filter,
	W2M\Import\Iterator;

/**
 * Class CommentProcessor
 *
 * @package W2M\Import\Service
 */
class CommentProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\CommentIterator
	 */
	private $iterator;

	/**
	 * @var Importer\CommentImporterInterface
	 */
	private $importer;

	/**
	 * @var Filter\CommentImportFilterInterface
	 */
	private $filter;

	/**
	 * @param Iterator\CommentIterator $iterator
	 * @param Importer\CommentImporterInterface $importer
	 * @param Filter\CommentImportFilterInterface $filter (Optional)
	 */
	public function __construct(
		Iterator\CommentIterator $iterator,
		Importer\CommentImporterInterface $importer,
		Filter\CommentImportFilterInterface $filter = NULL
	) {

		$this->iterator = $iterator;
		$this->importer = $importer;
		$this->filter   = $filter
			? $filter
			: new Filter\CommentPassThroughFilter;
	}

	/**
	 * Import comments and fires the action `w2m_import_comments_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		do_action( 'w2m_import_comments_start' );

		while ( $this->iterator->valid() ) {
			$import_comment = $this->iterator->current();
			if ( $import_comment && $this->filter->comment_to_import( $import_comment ) ) {
				$this->importer->import_comment( $import_comment );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_comments_done' );
	}
}