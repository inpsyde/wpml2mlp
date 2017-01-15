<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Term,
	stdClass;

/**
 * Class WpTermImporter
 *
 * @package W2M\Import\Service\Importer
 */
class WpTermImporter implements TermImporterInterface {

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 */
	public function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper
	) {

		$this->id_mapper = $id_mapper;
	}

	/**
	 * @param Type\ImportTermInterface $import_term
	 * @return bool|\WP_Error
	 */
	public function import_term( Type\ImportTermInterface $import_term ) {

		$local_parent_id = $this->id_mapper->local_id( 'term', $import_term->origin_parent_term_id() );

		$import_term_args = array(
			'description' => $import_term->description(),
			'parent'      => $local_parent_id,
			'slug'        => $import_term->slug()
		);

		$result = wp_insert_term(
			$import_term->name(),
			$import_term->taxonomy(),
			$import_term_args
		);
		if ( is_wp_error( $result ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param \WP_Error $result
			 * @param Type\ImportElementInterface $import_term
			 */
			do_action( 'w2m_import_term_error', $result, $import_term );

			return;
		}

		$import_term->id( $result[ 'term_id' ] );
		$wp_term = get_term_by( 'id', $result[ 'term_id' ], $import_term->taxonomy() );

		if ( $import_term->origin_parent_term_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Term $wp_term
			 * @param Type\ImportTermInterface $import_term
			 */
			do_action( 'w2m_import_missing_term_ancestor', $wp_term, $import_term );
		}

		/**
		 * @param stdClass|WP_Term
		 * @param Type\ImportTermInterface
		 */
		do_action( 'w2m_term_imported', $wp_term, $import_term );
	}

}