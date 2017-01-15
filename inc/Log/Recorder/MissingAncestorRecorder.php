<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Comment,
	WP_Post,
	WP_Term,
	Monolog,
	stdClass;

/**
 * Class MissingAncestorRecorder
 *
 * @package W2M\Log\Recorder
 */
class MissingAncestorRecorder implements MissingAncestorRecorderInterface {

	private $logger;

	public function __construct( Monolog\Logger $logger ) {

		$this->logger = $logger;
	}

	/**
	 * @wp-hook w2m_import_missing_term_ancestor
	 * @wp-hook w2m_import_missing_post_ancestor
	 * @wp-hook w2m_import_missing_comment_ancestor
	 *
	 * @param \WP_Term|\WP_Post|\WP_Comment|\stdClass $wp_object
	 * @param Type\ImportElementInterface $import_element
	 *
	 * @return void
	 */
	public function record( $wp_object, Type\ImportElementInterface $import_element ) {

		$type = get_class( $wp_object );
		$type_name = '';
		switch ( $type ) {
			case 'stdClass' :
			case 'WP_Term' :
				$type_name = 'term';
				break;

			case 'WP_Comment' :
				$type_name = 'comment';
				break;

			case 'WP_Post' :
				$type_name = 'post';
				break;

			default :
				$type_name = $type;
				break;
		}

		$message = "Cannot find parent {$type_name} for element (origin_id:%d, local_id:%d)";
		$message = sprintf(
			$message,
			$import_element->origin_id(),
			$import_element->id()
		);

		$this->logger->info( $message );
	}

}