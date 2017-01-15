<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	Monolog,
	WP_Error;

/**
 * Class ResolverErrorRecorder
 *
 * @package W2M\Log\Recorder
 */
class ResolverErrorRecorder implements WpErrorRecorderInterface {

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
	 * @wp-hook w2m_import_post_ancestor_resolver_error
	 * @wp-hook w2m_import_term_ancestor_resolver_error
	 *
	 * @param WP_Error $error
	 *
	 * @return void
	 */
	public function record( WP_Error $error ) {

		$code = $error->get_error_code();
		$msg  = $error->get_error_message( $code );
		$data = $error->get_error_data( $code );
		$data[ 'action' ] = current_filter();

		$this->log->warning( $msg, $data );
	}
}