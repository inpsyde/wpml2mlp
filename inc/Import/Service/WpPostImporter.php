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

		$local_parent_id = $this->id_mapper->local_id( 'post', $post->origin_parent_post_id() );

		$post->terms();
		$post->meta();
		$post->is_sticky();
		$post->origin_link();
		$post->locale_relations();

		$postdata = array(
			'post_title'            => $post->title(),
			'post_author'           => $post->origin_author_id(),
			'ping_status'           => $post->status(),
			'guid'                  => $post->guid(),
			'post_date'             => $post->date(),
			'comment_status'        => $post->comment_status(),
			'ping_status'           => $post->ping_status(),
			'post_type'             => $post->type(),
			'post_excerpt'          => $post->excerpt(),
			'post_content'          => $post->content(),
			'post_name'             => $post->name(),
			'post_parent'           => $local_parent_id,
			'menu_order'            => $post->menu_order(),
			'post_password'         => $post->password(),
		);

		$post_id = wp_insert_post( $postdata, TRUE );

		if ( is_wp_error( $post_id ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error
			 * @param Type\ImportPostInterface $post
			 */
			do_action( 'w2m_import_post_error', $post_id, $postdata );
			return;
		}

		$wp_post = get_post( $post_id, 'ARRAY_A' );

		if ( $post->origin_parent_term_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Term $wp_term
			 * @param Type\ImportTermInterface $term
			 */
			do_action( 'w2m_import_missing_term_ancestor', $wp_term, $term );
		}


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