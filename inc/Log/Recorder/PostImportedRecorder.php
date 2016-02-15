<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Post,
	Monolog;

/**
 * Class PostImportedRecorder
 *
 * @package W2M\Log\Recorder
 */
class PostImportedRecorder {

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
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		$this->logger->info(
			"Successfully imported post",
			[
				"type"      => $import_post->type(),
				"origin_id" => $import_post->origin_id(),
				"local_id"  => $import_post->id()
			]
		);
	}
}