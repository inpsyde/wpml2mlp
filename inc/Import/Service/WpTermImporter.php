<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Term,
	stdClass;


class WpTermImporter implements TermImporterInterface {

	/**
	 * @var Module\TranslationConnectorInterface
	 */
	private $translation_connector;

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * Todo: specify this
	 */
	private $ancestor_resolver;

	/**
	 * @param Module\TranslationConnectorInterface $translation_connector (Deprecated, Todo: remove it)
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param $ancestor_resolver (Not specified yet) (Deprecated, Todo: remove it)
	 */
	public function __construct(
		Module\TranslationConnectorInterface $translation_connector,
		Data\MultiTypeIdMapperInterface $id_mapper,
		$ancestor_resolver = NULL
	) {

		$this->translation_connector = $translation_connector;
		$this->id_mapper             = $id_mapper;
		$this->ancestor_resolver     = $ancestor_resolver;
	}

	/**
	 * @param Type\ImportTermInterface $term
	 * @return bool|\WP_Error
	 */
	public function import_term( Type\ImportTermInterface $term ) {

		$local_parent_id = $this->id_mapper->local_id( 'term', $term->origin_parent_term_id() );
		$term_args = array(
			'description' => $term->description(),
			'parent'      => $local_parent_id,
			'slug'        => $term->slug()
		);

		$result = wp_insert_term(
			$term->name(),
			$term->taxonomy(),
			$term_args
		);

		if ( is_wp_error( $result ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $result
			 * @param Type\ImportElementInterface $term
			 */
			do_action( 'w2m_import_term_error', $result, $term );
			return;
		}

		$term->id( $result[ 'term_id' ] );
		$wp_term = get_term_by( 'id', $result[ 'term_id' ], $term->taxonomy() );

		if ( $term->origin_parent_term_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Term $wp_term
			 * @param Type\ImportTermInterface $term
			 */
			do_action( 'w2m_import_missing_term_ancestor', $wp_term, $term );
		}

		/**
		 * @param stdClass|WP_Term
		 * @param Type\ImportTermInterface
		 */
		do_action( 'w2m_term_imported', $wp_term, $term );
	}

}