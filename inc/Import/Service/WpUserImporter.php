<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Error;


class WpUserImporter implements UserImporterInterface {

	/**/
	public function __construct() {}

	/**
	 * @param Type\ImportUserInterface $user
	 *
	 * @return bool|WP_Error
	 */
	public function import_user( Type\ImportUserInterface $user ) {


		$userdata = array(
			'user_login'    => $user->login(),
			'user_email'    => $user->email(),
			'first_name'    => $user->first_name(),
			'last_name'     => $user->last_name(),
			'display_name'  => $user->display_name()
		);

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $user_id
			 * @param Type\ImportElementInterface $userdata
			 */
			do_action( 'w2m_import_user_error', $user_id, $user );
			return;
		}


		/**
		 * @param WP_User $user_id
		 * @param Type\ImportUserInterface $user
		 */
		do_action( 'w2m_user_imported', $user_id, $userdata );

	}

}