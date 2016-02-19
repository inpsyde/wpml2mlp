<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Post;

/**
 * Interface PostImportListenerInterface
 *
 * Describes a listener to w2m_post_imported
 *
 * @package W2M\Import\Data
 */
interface PostImportListenerInterface {

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post( WP_Post $wp_post, Type\ImportPostInterface $import_post );
}