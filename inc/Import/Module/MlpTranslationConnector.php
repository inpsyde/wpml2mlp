<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Type,
	W2M\Import\Data,
	Mlp_Language_Api_Interface,
	WP_Error,
	WP_Post;

class MlpTranslationConnector implements TranslationConnectorInterface {

	/**
	 * @var Mlp_Language_Api_Interface
	 */
	private $mlp_language_api;

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @param Mlp_Language_Api_Interface $mlp_language_api
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 */
	public function __construct(
		Mlp_Language_Api_Interface $mlp_language_api,
		Data\MultiTypeIdMapperInterface $id_mapper
	) {

		$this->mlp_language_api = $mlp_language_api;
		$this->id_mapper        = $id_mapper;
	}

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param object $wp_term (stdClass, since WP 4.4 WP_Term)
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return void
	 */
	public function link_term( $wp_term, Type\ImportTermInterface $import_term ) {
		// TODO: Implement link_term() method.
	}

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function link_post( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {
		// TODO: Implement link_post() method.
	}
}