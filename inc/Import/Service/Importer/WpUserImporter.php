<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_User,
	WP_Error;

/**
 * Class WpUserImporter
 *
 * @package W2M\Import\Service\Importer
 */
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
	 * @param Type\ImportUserInterface $import_user
	 *
	 * @return bool|WP_Error
	 */
	public function import_user( Type\ImportUserInterface $import_user ) {

		$userdata = array(
			'user_login'    => $import_user->login(),
			'user_email'    => $import_user->email(),
			'first_name'    => $import_user->first_name(),
			'last_name'     => $import_user->last_name(),
			'display_name'  => $import_user->display_name()
		);

		$local_id = wp_insert_user( $userdata );

		if ( is_wp_error( $local_id ) ) {

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $local_id
			 * @param Type\ImportElementInterface $import_user
			 */
			do_action( 'w2m_import_user_error', $local_id, $import_user );
				return;
		}


		$import_user->id( $local_id );
		$wp_user = get_user_by( 'id', $local_id );

		/**
		 * @param WP_User $wp_user
		 * @param Type\ImportUserInterface $import_user
		 */
		do_action( 'w2m_user_imported', $wp_user, $import_user );

	}

}