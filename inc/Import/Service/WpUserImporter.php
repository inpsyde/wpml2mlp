<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Error;


class WpUserImporter implements UserImporterInterface {

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**/
	public function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper
	){

		$this->id_mapper = $id_mapper;

	}

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

		$wp_user = get_user_by( 'id', $user_id );
		$user->id( $user_id );

		/**
		 * @param WP_User $wp_user
		 * @param Type\ImportUserInterface $user
		 */
		do_action( 'w2m_user_imported', $wp_user, $userdata );

	}

}