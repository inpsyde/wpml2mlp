<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Log\Recorder,
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
	public function setup_logger() {

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
	}
}