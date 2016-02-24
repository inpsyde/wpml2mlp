<?php # -*- coding: utf-8 -*-

namespace W2M\Cli;

use
	W2M\Controller,
	W2M\Import,
	W2M\System,
	WP_CLI,
	WP_CLI_Command,
	WP_Error,
	Monolog,
	DateTime;

/**
 * Manages migration from WPML to MultilingualPress
 *
 * @package W2M\Cli
 */
class WpCliW2MCommand extends \WP_CLI_Command {

	/**
	 * Imports a single extended WXR file to a blog. Use the --url parameter to specify the home URL
	 * of the site you want to import the language to.
	 *
	 * Example: If you exported all spanish content (es_ES) to ~/my-site-es_ES.xml and want to import it
	 * to my-site.es use `wp w2m import ~/my-site-es_ES.xml --url=my-site.es
	 *
	 * When a map file (--map_file=<FILE>) is provided, user-import will be skipped
	 *
	 * ## Options
	 *
	 * <FILE>
	 * : Path to the WXR file
	 *
	 * @synopsis <FILE> --url=<url> [--no_confirm] [--verbose] [--map_file=<FILE>]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function import( Array $args, Array $assoc_args ) {

		if ( ! isset( $args[ 0 ] ) ) {
			$this->handle_error( new WP_Error( 'parameter', 'Missing parameter <FILE>' ) );
			exit;
		}

		$import_file = new Import\Common\File(
			realpath( $args[ 0 ] )
		);
		if ( ! is_file( $import_file->name() ) || ! is_readable( $import_file->name() ) ) {
			$this->handle_error( new WP_Error( 'parameter', 'Import file does not exist or is not readable.' ) );
			exit;
		}

		$defined_id_maps   = NULL;
		$map_file          = NULL;
		if ( isset( $assoc_args[ 'map_file' ] ) ) {
			$map_file = new Import\Common\File(
				realpath( $assoc_args[ 'map_file' ] )
			);
			if ( ! is_file( $map_file->name() ) || ! is_readable( $map_file->name() ) ) {
				$this->handle_error( new WP_Error( 'parameter', 'Map file does not exist or is not readable.' ) );
				exit;
			}
			// Todo: Use the given ID-Map types instead of this simple object
			$defined_id_maps = json_decode( file_get_contents( $map_file->name() ) );
		}

		$env = new System\ImportEnvironment;
		if ( ! $env->is_multisite() ) {
			$this->handle_error( new WP_Error( 'environment', 'This is not a multisite setup' ) );
			exit;
		}
		if ( ! $env->mlp_is_active() ) {
			$this->handle_error( new WP_Error( 'environment', 'MultilingualPress is not active' ) );
			exit;
		}

		$blog_id = get_current_blog_id();
		$locale = $env->mlp_blog_language( $blog_id );

		if ( ! isset( $assoc_args[ 'no_confirm' ] ) ) {
			$ays = readline( "Start import to blog {$blog_id}[{$locale}]? [yes]" );
			if ( 'yes' !== strtolower( $ays ) ) {
				WP_CLI::line( 'Aborting' );
				exit;
			}
		}

		$log_dir = WP_CONTENT_DIR.'/log';
		if ( !is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		//Todo: use DI-Container ASAP

		/**
		 * Logging
		 */
		$logger         = new Monolog\Logger( 'w2m-import' );
		$log_setup      = new System\LoggerSetup( $logger, $log_dir, 'w2m-import.log' );
		$log_controller = new Controller\TmpLogController( $logger );

		$log_setup->setup_handler();
		$log_controller->register_log_recorder();
		$log_controller->register_wp_cli_recorder();
		if ( isset( $assoc_args[ 'verbose' ] ) ) {
			$log_controller->register_wp_cli_handler();
		}

		/**
		 * ID mapping
		 */
		$import_id_mapper = new Import\Data\ImportListeningTypeIdMapper;
		if ( $defined_id_maps ) {
			$user_map = get_object_vars( $defined_id_maps->maps->users );
			$import_id_mapper = new Import\Data\PresetUserTypeIdMapper(
				$import_id_mapper,
				$user_map
			);
		}
		$ancestor_mapper   = new Import\Data\ImportListeningMTAncestorList;
		$mapper_controller = new Controller\DataIdObserverProvider(
			$import_id_mapper,
			$ancestor_mapper
		);
		$mapper_controller->register_id_observer();

		/**
		 * Translation linking
		 */
		// Todo: Find a better way to catch this API instance
		$mlp_content_relations_api  = new \Mlp_Content_Relations(
			$GLOBALS[ 'wpdb' ],
			new \Mlp_Site_Relations( $GLOBALS[ 'wpdb' ], 'mlp_site_relations' ),
			new \Mlp_Db_Table_Name(
				$GLOBALS[ 'wpdb' ]->base_prefix . 'multilingual_linked',
				new \Mlp_Db_Table_List( $GLOBALS[ 'wpdb' ] )
			)
		);
		$mlp_translation_connector = new Import\Module\MlpTranslationConnector(
			$mlp_content_relations_api,
			$import_id_mapper
		);
		$connector_provider = new Controller\TranslationConnectorProvider( $mlp_translation_connector );
		$connector_provider->register_connector();

		/**
		 * pending relations resolving
		 */
		$resolver = new Import\Module\ResolvingPendingRelations(
			$ancestor_mapper,
			new Import\Service\PostAncestorResolver( $import_id_mapper ),
			new Import\Service\TermAncestorResolver( $import_id_mapper )
		);
		$resolver_provider = new Controller\PendingRelationResolverProvider( $resolver );
		$resolver_provider->register_resolver();

		/**
		 * Import reporting
		 */
		$report       = new Import\Type\FileImportReport(
			$import_file,
			$import_id_mapper,
			$blog_id,
			[
				'map_file' => $map_file,
				'date'     => new DateTime,
				'name'     => 'WPML to MLP XML import report'
			]
		);
		$report_file   = new Import\Common\File( $log_dir . '/w2m-import-report-' . time() . '.json' );
		$report_writer = new Import\Module\JsonFileImportReport( $report , $report_file );
		add_action( 'w2m_import_process_done', [ $report_writer, 'create_report' ] );

		/**
		 * Users
		 */
		$user_iterator = new Import\Iterator\UserIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file->name(),
					'wp:author'
				)
			),
			new Import\Service\Parser\WpUserParser
		);
		$user_processor = new Import\Service\UserProcessor(
			$user_iterator,
			new Import\Service\Importer\WpUserImporter( $import_id_mapper )
		);

		/**
		 * Terms
		 */
		$term_iterator = new Import\Iterator\TermIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file->name(),
					'wp:category'
				)
			),
			new Import\Service\Parser\WpTermParser
		);
		$term_processor = new Import\Service\TermProcessor(
			$term_iterator,
			new Import\Service\Importer\WpTermImporter( $import_id_mapper ),
			new Import\Filter\DuplicateTermFilter
		);

		/**
		 * Posts
		 */
		$post_iterator = new Import\Iterator\PostIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file->name(),
					'item'
				)
			),
			new Import\Service\Parser\WpPostParser
		);
		$post_filter = new Import\Filter\DuplicatePostFilter;
		// Todo: make this assignment in a controller (provider)
		add_action( 'w2m_post_imported', [ $post_filter, 'record_post' ], 10, 2 );
		// Local development todo: Remove
		if ( 'http://wpml-to-mlp.dev' === site_url() ) {
			$post_filter = new Import\Filter\BlacklistPostTypeFilter( [ 'attachment' ], $post_filter );
			WP_CLI::warning( 'Skipping attachments' );
		}
		$post_processor = new Import\Service\PostProcessor(
			$post_iterator,
			new Import\Service\Importer\WpPostImporter( $import_id_mapper ),
			$post_filter
		);

		/**
		 * Comments
		 */
		$comment_iterator = new Import\Iterator\CommentIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file->name(),
					'wp:comment'
				)
			),
			new Import\Service\Parser\WpCommentParser
		);
		$comment_processor = new Import\Service\CommentProcessor(
			$comment_iterator,
			new Import\Service\Importer\WpCommentImporter( $import_id_mapper )
		);

		$processors = [
			$user_processor,
			$term_processor,
			$post_processor,
			$comment_processor
		];
		if ( $defined_id_maps ) {
			// if we have a map of user_ids, skip the user import
			array_shift( $processors );
		}
		$importer = new Import\Module\ElementImporter( $processors );

		/**
		 * @param Import\Type\FileImportReportInterface $report
		 */
		do_action( 'w2m_import_xml_start_process', $report );

		$importer->process_elements();

		/**
		 * @param Import\Type\FileImportReportInterface $report
		 */
		do_action( 'w2m_import_process_done', $report );

	}

	/**
	 * Todo: Will be used later
	 */
	private function die_on_missing_dependency() {

		$msg = 'A $GLOBAL variable is not in a state it supposed to be.'; //surprise
		exit(
		$this->handle_error(
			new WP_Error(
				1,
				$msg
			)
		)
		);
	}

	/**
	 * @param WP_Error $error
	 *
	 * @return int
	 */
	private function handle_error( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg ) {
			WP_CLI::error( $msg, FALSE );
		}

		return 1;
	}

	/**
	 * Todo: Will be used later
	 *
	 * @param WP_Error $error
	 *
	 * @return int
	 */
	private function handle_warning( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg ) {
			WP_CLI::warning( $msg );
		}

		return 1;
	}

	/**
	 * Todo: Will be used later
	 *
	 * @param $msg
	 *
	 * @return int
	 */
	private function handle_success( $msg ) {

		WP_CLI::success( $msg );

		return 0;
	}
}