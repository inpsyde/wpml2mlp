<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Type;

/**
 * Interface PostImporterInterface
 *
 * @package W2M\Import\Service\Importer
 */
interface PostImporterInterface {

	/**
	 * @param Type\ImportPostInterface $post
	 *
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $post );

}