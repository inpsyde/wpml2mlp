<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Term,
	Monolog,
	stdClass;

/**
 * Class TermImportedRecorder
 *
 * @package W2M\Log\Recorder
 */
class TermImportedRecorder {

	/**
	 * @var Monolog\Logger
	 */
	private $logger;

	/**
	 * @param Monolog\Logger $logger
	 */
	public function __construct( Monolog\Logger $logger ) {

		$this->logger = $logger;
	}

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param stdClass|WP_Term $wp_term
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record( $wp_term, Type\ImportTermInterface $import_term ) {

		$this->logger->info(
			"Successfully imported term",
			[
				"name"      => $import_term->name(),
				"taxonomy"  => $import_term->taxonomy(),
				"origin_id" => $import_term->origin_id(),
				"local_id"  => $import_term->id()
			]
		);
	}
}