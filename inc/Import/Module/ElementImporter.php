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

	/**
	 * @var Service\ElementProcessorInterface[] $processors
	 */
	private $processors = [];

	/**
	 * @param Service\ElementProcessorInterface[] $processors
	 */
	public function __construct( Array $processors ) {

		// the order is important for the performance of the import
		foreach ( $processors as $processor )
			$this->push_processor( $processor );
	}

	/**
	 * @param Service\ElementProcessorInterface $processor
	 */
	private function push_processor( Service\ElementProcessorInterface $processor ) {

		if ( ! in_array( $processor, $this->processors, TRUE ) )
			$this->processors[] = $processor;
	}

	/**
	 * Should fire an action when finished: `w2m_import_{type}s_done`
	 *
	 * @return void
	 */
	public function process_elements() {

		foreach ( $this->processors as $processor ) {
			$processor->process_elements();
		}
	}

}