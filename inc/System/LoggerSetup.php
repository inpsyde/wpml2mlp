<?php # -*- coding: utf-8 -*-

namespace W2M\System;

use
	Monolog;

/**
 * Class LoggerSetup
 *
 * Setup the logger.
 * Todo: make this somehow filterable
 *
 * @package W2M\System
 */
class LoggerSetup {

	/**
	 * @var Monolog\Logger
	 */
	private $logger;

	/**
	 * @var string
	 */
	private $log_directory;

	/**
	 * @var string
	 */
	private $default_log_file = 'w2m_import.log';

	/**
	 * @param Monolog\Logger $logger
	 * @param string $log_directory
	 * @param string $default_logfile
	 */
	public function __construct( Monolog\Logger $logger, $log_directory, $default_logfile = '' ) {

		$this->logger        = $logger;
		$this->log_directory = rtrim( $log_directory, '\\/' );
		if ( $default_logfile )
			$this->default_log_file = ltrim( $default_logfile, '\\/' );
	}

	/**
	 * Configures default handler
	 */
	public function setup_handler() {

		$default_log_file = "{$this->log_directory}/{$this->default_log_file}";
		$file_handler     = new Monolog\Handler\RotatingFileHandler( $default_log_file );
		$this->logger->pushHandler( $file_handler );
	}
}