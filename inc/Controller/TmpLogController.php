<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import\Common,
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Log\Handler,
	W2M\Log\Recorder,
	WP_Error,
	WP_CLI,
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

		$resolver_error_recorder = new Recorder\ResolverErrorRecorder( $this->logger );
		add_action( 'w2m_import_post_ancestor_resolver_error', [ $resolver_error_recorder, 'record' ] );
		add_action( 'w2m_import_term_ancestor_resolver_error', [ $resolver_error_recorder, 'record' ] );

		add_action( 'w2m_user_imported', [ new Recorder\UserImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_term_imported', [ new Recorder\TermImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_post_imported', [ new Recorder\PostImportedRecorder( $this->logger ), 'record' ], 10, 2 );
		add_action( 'w2m_comment_imported', [ new Recorder\CommentImportedRecorder( $this->logger ), 'record' ], 10, 2 );

		$mlp_connector_recorder = new Recorder\MlpLinkErrorRecorder( $this->logger );
		add_action( 'w2m_import_mlp_link_error', [ $mlp_connector_recorder, 'record' ] );

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
						'local_term_ids' => $term_ids,
						'action'         => current_filter()
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

				$logger->warning(
					$msg,
					[
						'local_post_id' => $local_post_id,
						'meta_key'      => $meta_key,
						'action'        => current_filter()
					]
				);
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
				$data[ 'action' ] = current_filter();

				$logger->warning( $msg, $data );
			},
			10,
			2
		);

		add_action(
			'w2m_import_request_attachment_error',
			/**
			 * @param WP_Error $error
			 * @param string $url
			 */
			function( WP_Error $error, $url ) use ( $logger ) {

				$code = $error->get_error_code();
				$msg  = $error->get_error_message( $code );
				$data = $error->get_error_data( $code );
				//don't log the body
				unset( $data[ 'body' ] );
				$data[ 'remote_url' ] = $url;
				$data[ 'action' ] = current_filter();

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

		add_action(
			'w2m_import_mlp_linked',
			function( $info ) use ( $logger ) {
				$this->logger->debug(
					"Linked {$info[ 'type' ]} translation",
					[
						'element_id'        => $info[ 'import_element' ]->id(),
						'blog_id'           => $info[ 'blog_id' ],
						'remote_locale'     => $info[ 'relation' ]->locale(),
						'remote_element_id' => $info[ 'remote_element_id' ],
						'remote_blog_id'    => $info[ 'remote_blog_id' ],
						'success'           => $info[ 'success' ]
					]
				);
			}
		);

		$mlp_logger = new Monolog\Logger(
			'mlp_debug',
			$this->logger->getHandlers(),
			$this->logger->getProcessors()
		);
		add_action(
			'mlp_debug',
			function( $msg ) use ( $mlp_logger ) {
				$mlp_logger->debug( $msg );
			}
		);

		add_action(
			'w2m_import_json_report_created',
			/**
			 * @param Common\FileInterface $file
			 */
			function( $file ) use ( $logger ) {

				$filename = basename( $file->name() );
				$this->logger->info( "Created report file {$filename}" );
			}
		);

		add_action(
			'w2m_import_xml_start_process',
			/**
			 * @param Type\FileImportReportInterface $import
			 */
			function( $import ) use ( $logger ) {
				$this->logger->info(
					"Start import",
					[
						'import_file' => basename( $import->import_file()->name() ),
						//Todo: add blog_id via $import
					]
				);
			}
		);

		add_action(
			'w2m_import_post_ancestor_resolving_start',
			function() use ( $logger ) {
				$logger->info( 'Resolving pending post ancestor relations' );
			}
		);

		add_action(
			'w2m_import_term_ancestor_resolving_start',
			function() use ( $logger ) {
				$logger->info( 'Resolving pending term ancestor relations' );
			}
		);
	}

	/**
	 * setup handler to listen to
	 */
	public function register_wp_cli_handler() {

		$handler = new Handler\WpCliHandler;
		$this->logger->pushHandler( $handler );
	}

	public function register_wp_cli_recorder() {

		$type_start_recorder = function() {
			$type = str_replace( 'w2m_import_', '', current_filter() );
			$type = str_replace( '_start', '', $type );
			$msg  = "Start importing {$type} …";
			WP_CLI::line( $msg );
		};
		$types = [ 'users', 'terms', 'posts', 'comments' ];
		foreach ( $types as $type ) {
			add_action( "w2m_import_{$type}_start", $type_start_recorder );
		}

		add_action(
			"w2m_import_process_done",
			function() {
				WP_CLI::success( "We're done. Thanks for choosing MultilingualPress." );
			}
		);
	}
}