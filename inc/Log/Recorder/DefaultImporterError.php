<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	Monolog,
	WP_Error;

/**
 * Class DefaultImporterError
 *
 * @package W2M\Log\Recorder
 */
class DefaultImporterError implements ImporterErrorInterface {

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
	 * @wp-hook w2m_import_post_error
	 * @wp-hook w2m_import_term_error
	 * @wp-hook w2m_import_user_error
	 * @wp-hook w2m_import_comment_error
	 *
	 * @param WP_Error $error
	 * @param Type\ImportElementInterface $import_element
	 *
	 * @return mixed
	 */
	public function record( WP_Error $error, Type\ImportElementInterface $import_element ) {

		$code = $error->get_error_code();
		$msg  = $error->get_error_message( $code );

		$this->log->warning(
			$msg,
			[
				'code'      => $code,
				'origin_id' => $import_element->origin_id(),
				'action'    => current_filter()
			]
		);
	}

}