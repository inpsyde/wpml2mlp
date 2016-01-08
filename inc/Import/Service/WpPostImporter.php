<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use W2M\Import\Data;
use W2M\Import\Type;

class WpPostImporter implements PostImporterInterface {

	/**
	 * @var TranslationConnectorInterface
	 */
	private $translation_connector;

	/**
	 * @var Data\IdMapperInterface
	 */
	private $id_mapper;

	/**
	 * Todo: specify this
	 */
	private $ancestor_resolver;

	/**
	 * @param TranslationConnectorInterface $translation_connector
	 * @param Data\IdMapperInterface $id_mapper
	 * @param $ancestor_resolver (Not specified yet)
	 */
	public function __construct(
		TranslationConnectorInterface $translation_connector,
		Data\IdMapperInterface $id_mapper,
		$ancestor_resolver = NULL
	) {

		$this->translation_connector = $translation_connector;
		$this->id_mapper             = $id_mapper;
		$this->ancestor_resolver     = $ancestor_resolver;
	}

	/**
	 * @param Type\ImportPostInterface $post
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $post ) {

		$post->terms();
		$post->meta();
		$post->is_sticky();
		$post->origin_link();
		$post->locale_relations();

		$post = array(
			'post_content'   => $post->content(),
			'post_name'      => $post->name(),
			'post_title'     => $post->title(),
			'post_status'    => $post->status(),
			'post_type'      => $post->type(),
			'post_author'    => $post->origin_author_id(),
			'ping_status'    => $post->ping_status(),
			'post_parent'    => $post->origin_parent_post_id(),
			'menu_order'     => $post->menu_order(),
			'post_password'  => $post->password(),
			'guid'           => $post->guid(),
			'post_excerpt'   => $post->excerpt(),
			'post_date'      => $post->date(),
			'comment_status' => $post->comment_status()
		);




		print_r( $post );

	}



}