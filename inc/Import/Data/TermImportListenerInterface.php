<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

use
	W2M\Import\Type,
	WP_Term,
	stdClass;

/**
 * Interface TermImportListenerInterface
 *
 * Describes a listener to w2m_term_imported
 *
 * @package W2M\Import\Data
 */
interface TermImportListenerInterface {

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param WP_Term|stdClass $wp_term
	 * @param Type\ImportTermInterface $import_term
	 */
	public function record_term( WP_Term $wp_term, Type\ImportTermInterface $import_term );
}