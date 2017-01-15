<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_User,
	Monolog;

/**
 * Class UserImportedRecorder
 *
 * @package W2M\Log\Recorder
 */
class UserImportedRecorder {

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
	 * @wp-hook w2m_user_imported
	 *
	 * @param WP_User $wp_user
	 * @param Type\ImportUserInterface $import_user
	 */
	public function record( WP_User $wp_user, Type\ImportUserInterface $import_user ) {

		$this->logger->info(
			"Successfully imported user",
			[
				"login"     => $import_user->login(),
				"origin_id" => $import_user->origin_id(),
				"local_id"  => $import_user->id()
			]
		);
	}
}