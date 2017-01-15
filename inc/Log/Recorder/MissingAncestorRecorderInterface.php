<?php # -*- coding: utf-8 -*-

namespace W2M\Log\Recorder;

use
	W2M\Import\Type,
	WP_Comment,
	WP_Post,
	WP_Term,
	stdClass;

/**
 * Interface MissingAncestorRecorderInterface
 *
 * @package W2M\Log\Recorder
 */
interface MissingAncestorRecorderInterface {

	/**
	 * @wp-hook w2m_import_missing_term_ancestor
	 * @wp-hook w2m_import_missing_post_ancestor
	 * @wp-hook w2m_import_missing_comment_ancestor
	 *
	 * @param WP_Term|WP_Post|WP_Comment|stdClass $wp_object
	 * @param Type\ImportElementInterface $import_element
	 *
	 * @return void
	 */
	public function record( $wp_object, Type\ImportElementInterface $import_element );
}