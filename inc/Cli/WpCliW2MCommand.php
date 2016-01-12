<?php # -*- coding: utf-8 -*-

namespace W2M\Cli;

use
	WP_CLI,
	WP_CLI_Command,
	WP_Error;

class WpCliW2MCommand extends \WP_CLI_Command {

	/**
	 * Imports a single extended WXR file to a blog.
	 *
	 * ## Options
	 *
	 * <FILE>
	 * : Path to the WXR file
	 *
	 * <BLOG_ID>
	 * : Id of the blog to import
	 *
	 * @synopsis <FILE> <BLOG_ID>
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function import( Array $args, Array $assoc_args ) {

		exit(
			$this->handle_warning( new WP_Error( 'msg', 'Not implemented yet' ) )
		);
	}


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
	 * @return int
	 */
	private function handle_error( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg )
			WP_CLI::error( $msg, FALSE );

		return 1;
	}
	/**
	 * @param WP_Error $error
	 * @return int
	 */
	private function handle_warning( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg )
			WP_CLI::warning( $msg );

		return 1;
	}
	/**
	 * @param $msg
	 * @return int
	 */
	private function handle_success( $msg ) {

		WP_CLI::success( $msg );

		return 0;
	}
}