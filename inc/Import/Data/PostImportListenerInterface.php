<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Post;

interface PostImportListenerInterface {

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post( WP_Post $wp_post, Type\ImportPostInterface $import_post );
}