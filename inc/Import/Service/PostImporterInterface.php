<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type;

interface PostImporterInterface {

	/**
	 * @param Type\ImportPostInterface $post
	 *
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $post );

}