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

		$postdata = array(
			'post_title'            => $post->title(),
			'post_author'           => $post->origin_author_id(),
			'post_status'           => $post->status(),
			'guid'                  => $post->guid(),
			'post_date_gmt'         => $post->date(),
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
			 * @param WP_Error $post_id
			 * @param Type\ImportElementInterface $postdata
			 */
			do_action( 'w2m_import_post_error', $post_id, $post );
			return;
		}

		$wp_post = get_post( $post_id );

		#if ( $post->origin_parent_term_id() && ! $local_parent_id ) {
		#	/**
		#	 * @param stdClass|WP_Term $wp_term
		#	 * @param Type\ImportTermInterface $term
		#	 */
		#	do_action( 'w2m_import_missing_term_ancestor', $wp_post, $post );
		#	return;
		#}


		$taxonomies = array();

		foreach( $post->terms() as $term ){

			$taxonomies[ $term['taxonomy'] ][] = $term['term_id'];

		}

		foreach( $taxonomies as $taxonomy => $term_ids ){

			$set_post_terms_result = wp_set_post_terms( $post_id, $term_ids, $taxonomy );

			if ( is_wp_error( $set_post_terms_result ) ) {

				/**
				 * Attach error handler/logger here
				 *
				 * @param WP_Error $set_post_terms_result
				 * @param int      $post_id
				 * @param array    $term_ids
				 * @param string   $taxonomy
				 */
				do_action( 'w2m_import_set_post_terms_error', $set_post_terms_result, $post_id, $term_ids, $taxonomy );

			}

		}

		$post_metas = $post->meta();

		print_r( $post_metas );

		$post->is_sticky();
		$post->origin_link();
		$post->locale_relations();

		foreach( $post->meta() as $meta ){

			$update_post_meta_result = update_post_meta( $post_id, $meta['key'], $meta['value'] );

			if ( $update_post_meta_result !== TRUE ) {

				#TODO: if $update_post_meta_result false turn it into a wp_error object

				/**
				 * Attach error handler/logger here
				 *
				 * @param boolean $update_post_meta_result
				 * @param int     $post_id
				 * @param array   $term_ids
				 * @param string  $taxonomy
				 */
				do_action( 'w2m_import_update_post_meta_error', $update_post_meta_result, $post_id, $meta[ 'key' ], $meta[ 'value' ] );
			}
		}




		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $post
		 */
		# do_action( 'w2m_post_imported', $wp_post, $post );

	}



}