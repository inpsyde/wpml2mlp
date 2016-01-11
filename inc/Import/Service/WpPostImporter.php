<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Post,
	WP_Error;


class WpPostImporter implements PostImporterInterface {

	/**
	 * Todo: remove
	 * @deprecated
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
	 * @param Module\TranslationConnectorInterface $translation_connector
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param $ancestor_resolver (Not specified yet)
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
	 * @param Type\ImportPostInterface $post
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $post ) {

		$post->terms();
		$post->meta();
		$post->is_sticky();
		$post->origin_link();
		$post->locale_relations();

		$postdata = array(
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

		$post_id = wp_insert_post( $postdata, TRUE );

		//Todo: Fire this action, when the result is an WP_Error and return.
		/**
		 * @param WP_Error $error
		 * @param Type\ImportPostInterface $post
		 */
		#do_action( 'w2m_import_post_error', $error, $post );

		print_r( get_post( $post_id ) );

		#$wp_post = get_post( $post_id );
		//Todo: Fire this action, when the origin_post_parent_id() cannot be resolved by the id_mapper

		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $post
		 */
		#do_action( 'w2m_import_missing_post_ancestor', $wp_post, $post );

		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $post
		 */
		# do_action( 'w2m_post_imported', $wp_post, $post );

	}



}