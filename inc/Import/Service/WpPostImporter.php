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
		$local_user_id = $this->id_mapper->local_id( 'user', $post->origin_author_id() );

		print_r( $local_user_id );

		$postdata = array(
			'post_title'            => $post->title(),
			'post_author'           => $local_user_id,
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

		if ( $post->origin_parent_post_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Post $wp_post
			 * @param Type\ImportPostInterface $post
			 */
			do_action( 'w2m_import_missing_post_ancestor', $post_id, $post );
			return;
		}


		$taxonomies = array();

		foreach( $post->terms() as $term ){

			$taxonomies[ $term->taxonomy() ][] = $term->origin_id();

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

		#Make this post sticky.
		if( $post->is_sticky() ){
			stick_post( $post_id );
		}

		update_post_meta( $post_id, '_w2m_origin_link', $post->origin_link() );

		foreach( $post->meta() as $meta ) {
			/* @var Type\ImportMetaInterface $meta */

			if ( $meta->is_single() ) {
				$update_post_meta_result = update_post_meta(
					$post_id,
					$meta->key(),
					$meta->value()
				);

				#test if update_post_meta returned a error
				$this->meta_result(
					$update_post_meta_result,
					array(
						'post_id' => $post_id,
						'meta' => array( 'key' => $meta->key(), 'value' => $meta->value() )
					)
				);

			} else {
				foreach ( $meta->value() as $v ) {
					add_post_meta(
						$post_id,
						$meta->key(),
						$v,
						FALSE // not unique
					);

					#test if update_post_meta returned a error
					$this->meta_result(
						$update_post_meta_result,
						array(
							'post_id' => $post_id,
							'meta' => array( 'key' => $meta->key(), 'value' => $v )
						)
					);

				}
			}

		}




		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $post
		 */
		do_action( 'w2m_post_imported', $wp_post, $post );

	}

	private function meta_result( $meta_result, $attribute ){

		if ( $meta_result !== TRUE ) {

			$meta_result = new WP_Error( 'broken', "Cant add or update postmeta." );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $meta_result
			 * @param int     $post_id
			 * @param array   $term_ids
			 * @param string  $taxonomy
			 */
			do_action( 'w2m_import_update_post_meta_error', $meta_result, $attribute['post_id'], $attribute['meta']['key'], $attribute['meta']['value'] );
		}

	}

}