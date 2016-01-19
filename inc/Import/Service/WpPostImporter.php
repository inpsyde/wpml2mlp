<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Post,
	WP_Error,
	WP_Http;

/**
 * Class WpPostImporter
 *
 * @package W2M\Import\Service
 */
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
		Data\MultiTypeIdMapperInterface $id_mapper
	) {

		$this->id_mapper  = $id_mapper;
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

		/**
		 * Its a attachment, check if all there wat we need for a import.
		 * (@see Type\ImportPostInterface $import_post)
		 */
		if( $import_post->type() == 'attachment' ) {

			if( ! empty( $import_post->origin_attachment_url() ) ){

				$local_id = wp_insert_post( $import_postdata, TRUE );

			}else{

				$error = new WP_Error( 'import_error', "The origin attachment url is empty." );

				/**
				 * Attach error handler/logger for missing origin_attachment_url
				 *
				 * @param WP_Error $error
				 * @param array $import_postdata
				 */
				do_action( 'w2m_import_attachment_missing_origin_attachment_url', $error, $import_postdata );
				return;

			}

		}else{

			$local_id = wp_insert_post( $import_postdata, TRUE );

		}

		if ( is_wp_error( $local_id ) ) {

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $local_id
			 * @param array $import_postdata
			 */
			do_action( 'w2m_import_post_error', $local_id, $import_postdata );
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
					$local_id,
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


		if( $import_post->type() == 'attachment' ){

			$this->import_attachment( $import_post, $import_post->origin_attachment_url() );

		}

		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $import_post
		 */
		do_action( 'w2m_post_imported', $wp_post, $import_post );

	}

	/**
	 * If update_meta result throw error a action is calling for logging the error
	 * @param $meta_result
	 * @param $attribute
	 */
	private function meta_result( $meta_result, $attribute ){

		if ( $meta_result !== TRUE ) {

			$meta_result = new WP_Error( 'meta_update_failed', "Cant add or update postmeta." );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $meta_result
			 * @param int     $attribute['post_id']
			 * @param string  $attribute['meta']['key']
			 * @param string  $attribute['meta']['value']
			 */
			do_action( 'w2m_import_update_post_meta_error', $meta_result, $attribute['post_id'], $attribute['meta']['key'], $attribute['meta']['value'] );
		}

	}

	/**
	 * Import attachments by origin attachemnt url
	 *
	 * @param int $import_post
	 * @param string $attachemnt_url
	 */
	private function import_attachment( Type\ImportPostInterface $import_post, $attachemnt_url ){

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $attachemnt_url ), null );

		// $filename should be the path to a file in the upload directory.
		$wp_upload = wp_upload_dir();

		$wp_upload_dir = $wp_upload['basedir'] . $wp_upload['subdir'];

		if( ! file_exists( $wp_upload_dir ) ){

			$mkdir = mkdir( $wp_upload_dir, 0777 , TRUE );

			if( ! $mkdir ) {

				$error = new WP_Error( 'mkdir_error', "Can't create uploads folder" );

				/**
				 * Attach error handler/logger here
				 *
				 * @param WP_Error $error
				 * @param string $wp_upload_dir
				 */
				do_action( 'w2m_import_attachment_mkidr_error', $error, $wp_upload_dir );
			}

		}

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( basename( $attachemnt_url ), 0, '', $import_post->date() );

		if ( $upload['error'] ) {

			$error = new WP_Error( 'upload_bits_error', $upload['error'] );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $error
			 * @param array $upload
			 */
			do_action( 'w2m_import_attachment_mkidr_error', $error, $upload );
			return;
		}


		// fetch the remote url and write it to the placeholder file
		$request = new WP_Http();
		$header = $request->request( $attachemnt_url, $upload['file'] );


		#rename( $upload['file'], $file_upload );

	}



}