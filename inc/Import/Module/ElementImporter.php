<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Service;

/**
 * Class ElementImporter
 *
 * Composite of the four processor services
 *
 * @package W2M\Import\Module
 */
class ElementImporter implements Service\ElementProcessorInterface {

	private $processors = [];

	public function __construct(
		Service\UserProcessor $user,
		Service\TermProcessor $term,
		Service\PostProcessor $post,
		Service\CommentProcessor $comment
	) {

		// the order is important for the performance of the import
		$this->processors = [
			$user,
			$term,
			$post,
			$comment
		];
	}

	/**
	 * Should fire an action when finished: `w2m_import_{type}s_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		foreach ( $this->processors as $processor ) {
			/* @var Service\ElementProcessorInterface $processor */
			$processor->process_elements();
		}
	}

}