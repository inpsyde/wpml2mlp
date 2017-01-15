<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Filter,
	W2M\Import\Iterator;

/**
 * Class PostProcessor
 *
 * @package W2M\Import\Service
 */
class PostProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\PostIterator
	 */
	private $iterator;

	/**
	 * @var Importer\WpPostImporter
	 */
	private $importer;

	/**
	 * @var Filter\PostImportFilterInterface
	 */
	private $filter;

	/**
	 * @param Iterator\PostIterator $iterator
	 * @param Importer\PostImporterInterface $importer
	 * @param Filter\PostImportFilterInterface $filter (Optional)
	 */
	public function __construct(
		Iterator\PostIterator $iterator,
		Importer\PostImporterInterface $importer,
		Filter\PostImportFilterInterface $filter = NULL
	) {
		$this->iterator = $iterator;
		$this->importer = $importer;
		$this->filter   = $filter
			? $filter
			: new Filter\PostPassThroughFilter;
	}

	/**
	 * Import posts and fires the action `w2m_import_posts_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		do_action( 'w2m_import_posts_start' );

		while ( $this->iterator->valid() ) {
			$import_post = $this->iterator->current();
			if ( $import_post && $this->filter->post_to_import( $import_post ) ) {
				$this->importer->import_post( $import_post );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_posts_done' );
	}

}