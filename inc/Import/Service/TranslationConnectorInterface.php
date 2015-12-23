<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	WP_Error,
	WP_Post;

interface TranslationConnectorInterface {

	/**
	 * @param object $new_term (stdClass, since WP 4.4 WP_Term)
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return bool|WP_Error
	 */
	public function link_term( $new_term, Type\ImportTermInterface $import_term );

	/**
	 * @param WP_Post $new_post
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return bool|WP_Error
	 */
	public function link_post( WP_Post $new_post, Type\ImportPostInterface $import_post );
}