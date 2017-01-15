<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	WP_Error,
	Monolog;

/**
 * Class MlpLinkErrorRecorder
 *
 * @package W2M\Log\Recorder
 */
class MlpLinkErrorRecorder implements WpErrorRecorderInterface {

	/**
	 * @var Monolog\Logger
	 */
	private $logger;

	public function __construct( Monolog\Logger $logger ) {

		$this->logger = $logger;
	}

	/**
	 * @wp-hook w2m_import_mlp_link_error
	 *
	 * @param WP_Error $error
	 *
	 * @return void
	 */
	public function record( WP_Error $error ) {

		$code = $error->get_error_code();
		$data = $error->get_error_data( $code );

		$this->logger->notice(
			$error->get_error_message( $code ),
			[
				'origin_id' => $data[ 'data' ][ 'locale_relation' ]->origin_id(),
				'locale'    => $data[ 'data' ][ 'locale_relation' ]->locale(),
				'element_id'=> $data[ 'data' ][ 'import_element' ]->id()
			]
		);
	}

}