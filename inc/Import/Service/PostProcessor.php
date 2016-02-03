<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Iterator;

class PostProcessor implements ElementProcessorInterface {

	/**
	 * @var Iterator\PostIterator
	 */
	private $iterator;

	/**
	 * @var WpPostImporter
	 */
	private $importer;

	/**
	 * @param Iterator\PostIterator $iterator
	 * @param PostImporterInterface $importer
	 */
	public function __construct(
		Iterator\PostIterator $iterator,
		PostImporterInterface $importer
	) {
		$this->iterator = $iterator;
		$this->importer = $importer;
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
			if ( $import_post ) {
				$this->importer->import_post( $import_post );
			}
			$this->iterator->next();
		}

		do_action( 'w2m_import_posts_done' );
	}

}