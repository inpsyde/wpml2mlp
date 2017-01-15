<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Handler;

use
	WP_CLI,
	Monolog,
	Monolog\Handler,
	Monolog\Formatter;

/**
 * Class WpCliHandler
 *
 * @package W2M\Log\Handler
 */
class WpCliHandler extends Handler\AbstractProcessingHandler implements Handler\HandlerInterface {

	/**
	 * Level from when to use WP_CLI::warning() method instead of WP_CLI::line()
	 *
	 * @var int
	 */
	private $waring_level = Monolog\Logger::WARNING;

	/**
	 * Level from when to use WP_CLI::error() method instead of WP_CLI::warning()
	 *
	 * @var int
	 */
	private $error_level = Monolog\Logger::ERROR;

	/**
	 * @param int $level (Optional, default to Monolog\Logger::WARNING)
	 * @param bool $bubble (Optional, default to TRUE)
	 * @param int $warning_level (Optional, default to Monolog\Logger::WARNING)
	 * @param int $error_level (Optional, default to Monolog\Logger::ERROR)
	 */
	public function __construct(
		$level = Monolog\Logger::WARNING,
		$bubble = TRUE,
		$warning_level = Monolog\Logger::WARNING,
		$error_level = Monolog\Logger::ERROR
	) {

		$this->waring_level = (int) $warning_level;
		$this->error_level  = (int) $error_level;

		parent::__construct( (int) $level, (bool) $bubble );
	}

	/**
	 * @param array $record {
	 *      int $level
	 *      string $channel
	 *      string $message
	 * }
	 */
	protected function write( array $record ) {

		if ( $this->formatter ) {
			$msg = $this->formatter->format( $record );
		} else {
			$msg = "[{$record[ 'channel' ]}] {$record[ 'message' ]}";
		}

		if ( $this->waring_level <= $record[ 'level' ] ) {
			WP_CLI::warning( $msg );

			return;
		}

		if ( $this->error_level <= $record[ 'level' ] ) {
			WP_CLI::error( $msg, FALSE );

			return;
		}

		WP_CLI::line( $msg );
	}

}