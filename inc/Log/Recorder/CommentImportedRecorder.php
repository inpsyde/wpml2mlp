<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Comment,
	Monolog,
	stdClass;

/**
 * Class CommentImportedRecorder
 *
 * @package W2M\Log\Recorder
 */
class CommentImportedRecorder {

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
	 * @wp-hook w2m_comment_imported
	 *
	 * @param stdClass|WP_Comment $wp_comment
	 * @param Type\ImportCommentInterface $import_comment
	 */
	public function record( $wp_comment, Type\ImportCommentInterface $import_comment ) {

		$this->logger->info(
			"Successfully imported comment",
			[
				"origin_id"      => $import_comment->origin_id(),
				"local_id"       => $import_comment->id(),
				"origin_post_id" => $import_comment->origin_post_id(),
				"local_post_id"  => $wp_comment->comment_post_ID
			]
		);
	}
}