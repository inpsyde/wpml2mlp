<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Type,
	WP_Error;

/**
 * Interface UserImporterInterface
 *
 * @package W2M\Import\Service
 */
interface UserImporterInterface {

	/**
	 * @param Type\ImportUserInterface $user
	 *
	 * @return bool|WP_Error
	 */
	public function import_user( Type\ImportUserInterface $user );
}