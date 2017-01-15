<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	Monolog,
	WP_Error;

/**
 * Class DefaultElementParserError
 *
 * @package W2M\Log\Recorder
 */
class DefaultElementParserError implements WpErrorRecorderInterface {

	/**
	 * @var Monolog\Logger
	 */
	private $log;

	/**
	 * @param Monolog\Logger $log
	 */
	public function __construct( Monolog\Logger $log ) {

		$this->log = $log;
	}

	/**
	 * @wp-hook w2m_import_parse_term_error
	 * @wp-hook w2m_import_parse_post_error
	 * @wp-hook w2m_import_parse_user_error
	 * @wp-hook w2m_import_parse_comment_error
	 *
	 * @param WP_Error $error
	 *
	 * @return void
	 */
	public function record( WP_Error $error ) {

		$code = $error->get_error_code();
		$msg  = $error->get_error_message( $code );

		$this->log->warning( $msg, [ 'action' => current_filter() ] );
	}
}