<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Log\Recorder,
	W2M\Import\Type,
	WP_Error,
	Monolog;

/**
 * Class TmpLogController
 *
 * This class is a temporary solution. It's in fact a Courier for the logger which is considered
 * an anti pattern. Don't rely on it, it will be refactored soon!
 *
 * @package W2M\Controller
 */
class TmpLogController {

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
	 * setup and assign all log recorder
	 */
	public function register_log_recorder() {

		$parser_error_recorder = new Recorder\DefaultElementParserError( $this->logger );
		add_action( 'w2m_import_parse_user_error',    [ $parser_error_recorder, 'record' ] );
		add_action( 'w2m_import_parse_term_error',    [ $parser_error_recorder, 'record' ] );
		add_action( 'w2m_import_parse_post_error',    [ $parser_error_recorder, 'record' ] );
		add_action( 'w2m_import_parse_comment_error', [ $parser_error_recorder, 'record' ] );

		$importer_error_recorder = new Recorder\DefaultImporterError( $this->logger );
		add_action( 'w2m_import_user_error',    [ $importer_error_recorder, 'record' ], 10, 2 );
		add_action( 'w2m_import_term_error',    [ $importer_error_recorder, 'record' ], 10, 2 );
		add_action( 'w2m_import_post_error',    [ $importer_error_recorder, 'record' ], 10, 2 );
		add_action( 'w2m_import_comment_error', [ $importer_error_recorder, 'record' ], 10, 2 );

		$missing_ancestor_recorder = new Recorder\MissingAncestorRecorder( $this->logger );
		add_action( 'w2m_import_missing_term_ancestor', [ $missing_ancestor_recorder, 'record' ], 10, 2 );
		add_action( 'w2m_import_missing_post_ancestor', [ $missing_ancestor_recorder, 'record' ], 10, 2 );
		add_action( 'w2m_import_missing_comment_ancestor', [ $missing_ancestor_recorder, 'record' ], 10, 2 );

		add_action( 'w2m_user_imported', [ new Recorder\UserImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_term_imported', [ new Recorder\TermImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_post_imported', [ new Recorder\PostImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_comment_imported', [ new Recorder\CommentImportedRecorder( $this->logger ), 'record' ], 10, 2 );

		$logger = $this->logger;
		add_action(
			'w2m_import_set_post_terms_error',
			/**
			 * @param WP_Error $set_post_terms_result
			 * @param int $local_post_id
			 * @param array $term_ids
			 * @param string $taxonomy
			 */
			function( WP_Error $error, $local_post_id, $term_ids, $taxonomy ) use ( $logger ) {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );

				$logger->warning(
					$msg,
					[
						'local_post_id'  => $local_post_id,
						'taxonomy'       => $taxonomy,
						'local_term_ids' => $term_ids
					]
				);
			},
			10,
			4
		);
		add_action(
			'w2m_import_update_post_meta_error',
			/**
			 * @param WP_Error $meta_result
			 * @param int $local_post_id
			 * @param string $meta_key
			 * @param string $meta_value
			 */
			function( WP_Error $error, $local_post_id, $meta_key, $meta_value ) use ( $logger ) {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );

				$logger->warning( $msg, [ 'local_post_id' => $local_post_id, 'meta_key' => $meta_key ] );
			},
			10,
			4
		);

		add_action(
			'w2m_import_attachment_mkdir_error',
			/**
			 * @param WP_Error $error
			 * @param array $data
			 */
			function( WP_Error $error, $data ) use ( $logger ) {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );

				$logger->warning( $msg, $data );
			},
			10,
			2
		);

		add_action(
			'w2m_import_request_attachment_error',
			/**
			 * @param WP_Error $error
			 * @param array $data
			 */
			function(WP_Error $error, $data ) use ( $logger ) {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );

				$logger->warning( $msg, $data );

			},
			10,
			2
		);

		add_action(
			'w2m_attachment_imported',
			/**
			 * @param array $uploaded
			 * @param Type\ImportPostInterface $import_post
			 */
			function( $upload, $import_post ) use ( $logger ) {
				$logger->info( "Attachment file downloaded (id: {$import_post->id()}", $upload );
			},
			10,
			2
		);

		// for debugging
		add_action(
			'w2m_import_set_comment_id',
			/**
			 * @param Type\ImportCommentInterface $import_comment
			 */
			function( Type\ImportCommentInterface $import_comment ) use ( $logger ) {

				$logger->debug(
					"Imported post id recorded",
					[
						'origin_id' => $import_comment->origin_id(),
						'local_id'  => $import_comment->id()
					]
				);
			}
		);
		add_action(
			'w2m_import_set_post_id',
			/**
			 * @param Type\ImportPostInterface $import_post
			 */
			function( Type\ImportPostInterface $import_post ) use ( $logger ) {

				$logger->debug(
					"Imported post id recorded",
					[
						'origin_id' => $import_post->origin_id(),
						'local_id'  => $import_post->id()
					]
				);
			}
		);
		add_action(
			'w2m_import_set_term_id',
			/**
			 * @param Type\ImportUserInterface $import_term
			 */
			function( Type\ImportTermInterface $import_term ) use ( $logger ) {

				$logger->debug(
					"Imported term id recorded",
					[
						'origin_id' => $import_term->origin_id(),
						'local_id'  => $import_term->id()
					]
				);
			}
		);
		add_action(
			'w2m_import_set_user_id',
			/**
			 * @param Type\ImportUserInterface $import_user
			 */
			function( Type\ImportUserInterface $import_user ) use ( $logger ) {

				$logger->debug(
					"Imported user id recorded",
					[
						'origin_id' => $import_user->origin_id(),
						'local_id'  => $import_user->id()
					]
				);
			}
		);

		add_action(
			'w2m_import_missing_post_local_user_id',
			/**
			 * @param WP_Error
			 * @param Type\ImportPostInterface
			 */
			function( WP_Error $error, Type\ImportPostInterface $import_post ) use ( $logger )  {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );

				$logger->warning(
					$msg,
					[
						'origin_post_id' => $import_post->origin_id(),
						'type' => $import_post->type()
					]
				);
			},
			10,
			2
		);
	}
}