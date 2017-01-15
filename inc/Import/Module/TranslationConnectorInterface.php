<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Type,
	WP_Post;

/**
 * Interface TranslationConnectorInterface
 *
 * @package W2M\Import\Module
 */
interface TranslationConnectorInterface {

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param object $wp_term (stdClass, since WP 4.4 WP_Term)
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return void
	 */
	public function link_term( $wp_term, Type\ImportTermInterface $import_term );

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function link_post( WP_Post $wp_post, Type\ImportPostInterface $import_post );
}