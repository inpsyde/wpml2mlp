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
	 * Todo: remove
	 * @deprecated
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
	 * @param Type\ImportPostInterface $import_post
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $import_post ) {

		$local_parent_id = $this->id_mapper->local_id( 'post', $import_post->origin_parent_post_id() );
		$local_user_id = $this->id_mapper->local_id( 'user', $import_post->origin_author_id() );

		$import_postdata = array(
			'post_title'            => $import_post->title(),
			'post_author'           => $local_user_id,
			'post_status'           => $import_post->status(),
			'guid'                  => $import_post->guid(),
			'post_date_gmt'         => $import_post->date(),
			'comment_status'        => $import_post->comment_status(),
			'ping_status'           => $import_post->ping_status(),
			'post_type'             => $import_post->type(),
			'post_excerpt'          => $import_post->excerpt(),
			'post_content'          => $import_post->content(),
			'post_name'             => $import_post->name(),
			'post_parent'           => $local_parent_id,
			'menu_order'            => $import_post->menu_order(),
			'post_password'         => $import_post->password(),
		);

		$local_id = wp_insert_post( $import_postdata, TRUE );

		if ( is_wp_error( $local_id ) ) {

			$error = $local_id;

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $error
			 * @param Type\ImportElementInterface $import_postdata
			 */
			do_action( 'w2m_import_post_error', $error, $import_post );
			return;
		}

		$wp_post = get_post( $local_id );

		if ( $import_post->origin_parent_post_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Post $wp_post
			 * @param Type\ImportPostInterface $import_post
			 */
			do_action( 'w2m_import_missing_post_ancestor', $wp_post, $import_post );
			return;
		}


		$taxonomies = array();

		foreach( $import_post->terms() as $term ){

			$taxonomies[ $term->taxonomy() ][] = $term->origin_id();

		}

		foreach( $taxonomies as $taxonomy => $term_ids ){

			$set_post_terms_result = wp_set_post_terms( $local_id, $term_ids, $taxonomy );

			if ( is_wp_error( $set_post_terms_result ) ) {

				/**
				 * Attach error handler/logger here
				 *
				 * @param WP_Error $set_post_terms_result
				 * @param int      $import_post_id
				 * @param array    $term_ids
				 * @param string   $taxonomy
				 */
				do_action( 'w2m_import_set_post_terms_error', $set_post_terms_result, $local_id, $term_ids, $taxonomy );

			}

		}

		#Make this post sticky.
		if( $import_post->is_sticky() ){
			stick_post( $local_id );
		}

		update_post_meta( $local_id, '_w2m_origin_link', $import_post->origin_link() );

		foreach( $import_post->meta() as $meta ) {
			/* @var Type\ImportMetaInterface $meta */

			if ( $meta->is_single() ) {
				$update_post_meta_result = update_post_meta(
					$import_post_id,
					$meta->key(),
					$meta->value()
				);

				#test if update_post_meta returned a error
				$this->meta_result(
					$update_post_meta_result,
					array(
						'post_id' => $local_id,
						'meta' => array( 'key' => $meta->key(), 'value' => $meta->value() )
					)
				);

			} else {
				foreach ( $meta->value() as $v ) {
					add_post_meta(
						$local_id,
						$meta->key(),
						$v,
						FALSE // not unique
					);

					#test if update_post_meta returned a error
					$this->meta_result(
						$update_post_meta_result,
						array(
							'post_id' => $local_id,
							'meta' => array( 'key' => $meta->key(), 'value' => $v )
						)
					);

				}
			}

		}

		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $import_post
		 */
		do_action( 'w2m_post_imported', $wp_post, $import_post );

	}

	private function meta_result( $meta_result, $attribute ){

		if ( $meta_result !== TRUE ) {

			$meta_result = new WP_Error( 'meta_update_failed', "Cant add or update postmeta." );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $meta_result
			 * @param int     $import_post_id
			 * @param array   $term_ids
			 * @param string  $taxonomy
			 */
			do_action( 'w2m_import_update_post_meta_error', $meta_result, $attribute['post_id'], $attribute['meta']['key'], $attribute['meta']['value'] );
		}

	}

}