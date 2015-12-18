<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

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
	 * @var Data\IdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @param Mlp_Language_Api_Interface $mlp_language_api
	 * @param Data\IdMapperInterface $id_mapper
	 */
	public function __construct(
		Mlp_Language_Api_Interface $mlp_language_api,
		Data\IdMapperInterface $id_mapper
	) {

		$this->mlp_language_api = $mlp_language_api;
		$this->id_mapper        = $id_mapper;
	}
	/**
	 * @param $new_term
	 * @param Type\ImportTermInterface $import_term
	 * @return bool|WP_Error
	 */
	public function link_term( $new_term, Type\ImportTermInterface $import_term ) {
		// TODO: Implement link_term() method.
	}

	/**
	 * @param WP_Post $new_post
	 * @param Type\ImportPostInterface $import_post
	 * @return bool|WP_Error
	 */
	public function link_post( WP_Post $new_post, Type\ImportPostInterface $import_post ) {
		// TODO: Implement link_post() method.
	}

}