<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service\Importer;

use
	W2M\Import\Data,
	W2M\Import\Type,
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
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @var WP_Http $http
	 */
	private $http;

	/**
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param WP_Http $http (Optional)
	 */
	public function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper,
		WP_Http $http = NULL
	) {

		$this->id_mapper = $id_mapper;
		$this->http      = $http ? $http : new WP_Http;
	}

	/**
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return bool|WP_Error
	 */
	public function import_post( Type\ImportPostInterface $import_post ) {

		$local_parent_id = $this->id_mapper->local_id( 'post', $import_post->origin_parent_post_id() );
		$local_user_id   = $this->id_mapper->local_id( 'user', $import_post->origin_author_id() );

		/**
		 * trigger action if local user id not solved
		 */
		if ( ! $local_user_id ) {

			$error = new WP_Error( 'local_user_id_missing', "Local user is empty or 0." );

			/**
			 * @param WP_Post $wp_post
			 * @param Type\ImportPostInterface $import_post
			 */
			do_action( 'w2m_import_missing_post_local_user_id', $error, $import_post );
		}

		$import_postdata = array(
			'post_title'     => $import_post->title(),
			'post_author'    => $local_user_id,
			'post_status'    => $import_post->status(),
			'guid'           => $import_post->guid(),
			'post_date_gmt'  => $import_post->date()->format( 'Y-m-d H:i:s' ),
			'comment_status' => $import_post->comment_status(),
			'ping_status'    => $import_post->ping_status(),
			'post_type'      => $import_post->type(),
			'post_excerpt'   => $import_post->excerpt(),
			'post_content'   => $import_post->content(),
			'post_name'      => $import_post->name(),
			'post_parent'    => $local_parent_id,
			'menu_order'     => $import_post->menu_order(),
			'post_password'  => $import_post->password(),
		);

		/**
		 * It's an attachment, check if all is there what we need for an import.
		 * (@see Type\ImportPostInterface $import_post)
		 */
		if ( $import_post->type() == 'attachment' ) {

			if ( $import_post->origin_attachment_url() ) {

				/**
				 * @param array $import_postdata Arguments for inserting an attachment. @see wp_insert_post
				 * @param string $origin_attachment_url Filename.
				 * @param int $local_parent_id Parent post ID.
				 *
				 * @return int Attachment ID.
				 **/
				$local_id = wp_insert_attachment(
					$import_postdata, $import_post->origin_attachment_url(), $local_parent_id
				);

			} else {

				$error = new WP_Error( 'import_error', "The origin attachment url is empty." );
				/**
				 * Attach error handler/logger for missing origin_attachment_url
				 *
				 * @param WP_Error $error
				 * @param Type\ImportPostInterface $import_post
				 */
				do_action( 'w2m_import_attachment_missing_origin_attachment_url', $error, $import_post );

				return;

			}

		} else {
			$local_id = wp_insert_post( $import_postdata, TRUE );
		}

		if ( is_wp_error( $local_id ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $local_id
			 * @param Type\ImportPostInterface $import_post
			 */
			do_action( 'w2m_import_post_error', $local_id, $import_post );

			return $local_id;
		}

		// notify the import object about the new local post id
		$import_post->id( $local_id );

		$wp_post = get_post( $local_id );

		if ( $import_post->origin_parent_post_id() && !$local_parent_id ) {
			/**
			 * @param \stdClass|WP_Post $wp_post
			 * @param Type\ImportPostInterface $import_post
			 */
			do_action( 'w2m_import_missing_post_ancestor', $wp_post, $import_post );
		}

		$taxonomies = array();

		foreach ( $import_post->terms() as $term ) {

			$local_term = $this->id_mapper
				->local_id( 'term',  $term->origin_id() );

			// fallback if term_id 0
			if ( $local_term == 0 ) {

				$local_term_obj = get_term_by( 'slug', $term->nicename(), $term->taxonomy() );
				$taxonomies[ $term->taxonomy() ][] = $local_term_obj->term_id;

			} else {

				$taxonomies[ $term->taxonomy() ][] = $this->id_mapper
					->local_id( 'term',  $term->origin_id() );
				// Todo: Trigger error when ID could not be resolved

			}

		}

		foreach ( $taxonomies as $taxonomy => $term_ids ) {

			$set_post_terms_result = wp_set_post_terms( $local_id, $term_ids, $taxonomy );
			if ( is_wp_error( $set_post_terms_result ) ) {
				/**
				 * Attach error handler/logger here
				 *
				 * @param WP_Error $set_post_terms_result
				 * @param int $import_post_id
				 * @param array $term_ids
				 * @param string $taxonomy
				 */
				do_action( 'w2m_import_set_post_terms_error', $set_post_terms_result, $local_id, $term_ids, $taxonomy );
			}
		}

		# Make this post sticky.
		if ( $import_post->is_sticky() ) {
			stick_post( $local_id );
		}

		update_post_meta( $local_id, '_w2m_origin_link', $import_post->origin_link() );

		foreach ( $import_post->meta() as $meta ) {
			if ( $meta->is_single() ) {
				$update_post_meta_result = update_post_meta(
					$local_id,
					$meta->key(),
					$meta->value()
				);
				if ( $update_post_meta_result ) {
					continue;
				}

				$this->propagate_import_meta_error(
					array(
						'result'  => $update_post_meta_result,
						'post_id' => $local_id,
						'meta'    => array( 'key' => $meta->key(), 'value' => $meta->value() )
					)
				);
			} else {
				foreach ( $meta->value() as $v ) {
					$add_post_meta_result = add_post_meta(
						$local_id,
						$meta->key(),
						$v,
						FALSE // not unique
					);
					if ( $add_post_meta_result ) {
						continue;
					}

					$this->propagate_import_meta_error(
						array(
							'result'  => $add_post_meta_result,
							'post_id' => $local_id,
							'meta'    => array( 'key' => $meta->key(), 'value' => $v )
						)
					);
				}
			}
		}

		if ( $import_post->type() == 'attachment' ) {
			$this->import_attachment( $local_id, $import_post );
		}

		/**
		 * @param WP_Post $wp_post
		 * @param Type\ImportPostInterface $import_post
		 */
		do_action( 'w2m_post_imported', $wp_post, $import_post );

		return TRUE;
	}

	/**
	 * If update_meta result throw error a action is calling for logging the error
	 *
	 * @param Array $attribute
	 */
	private function propagate_import_meta_error( Array $attribute ) {

		$meta_result = new WP_Error( 'meta_update_failed', "Can't add or update postmeta." );
		/**
		 * Attach error handler/logger here
		 *
		 * @param WP_Error $meta_result
		 * @param int $attribute ['post_id']
		 * @param string $attribute ['meta']['key']
		 * @param string $attribute ['meta']['value']
		 */
		do_action(
			'w2m_import_update_post_meta_error',
			$meta_result,
			$attribute[ 'post_id' ],
			$attribute[ 'meta' ][ 'key' ],
			$attribute[ 'meta' ][ 'value' ]
		);

	}

	/**
	 * Import attachments by origin attachemnt url
	 *
	 * @param int $attachment_id
	 * @param Type\ImportPostInterface $import_post
	 */
	public function import_attachment( $attachment_id, Type\ImportPostInterface $import_post ) {

		$attachment_url = $import_post->origin_attachment_url();

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $attachment_url ), NULL );

		// $filename should be the path to a file in the upload directory.
		$wp_upload = wp_upload_dir();

		$wp_upload_dir = $wp_upload[ 'basedir' ] . $wp_upload[ 'subdir' ];

		if ( !file_exists( $wp_upload_dir ) ) {

			// Todo: use wp_mkdir_p()
			$mkdir = mkdir( $wp_upload_dir, 0777, TRUE );

			if ( !$mkdir ) {

				$error = new WP_Error( 'mkdir_error', "Can't create uploads folder" );

				/**
				 * Attach error handler/logger here
				 *
				 * @param WP_Error $error
				 * @param array $data
				 */
				do_action( 'w2m_import_attachment_mkdir_error', $error, [ 'wp_upload_dir' => $wp_upload_dir ] );
			}

		}

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits(
			basename( $attachment_url ),
			0,
			'',
			$import_post->date()->format( 'Y/m' )
		);

		if ( $upload[ 'error' ] ) {

			$upload[ 'file' ] = basename( $attachment_url );
			$error = new WP_Error( 'upload_bits_error', $upload[ 'error' ], $upload );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $error
			 * @param array $upload {
			 *     string $error
			 *     string $file
			 * }
			 */
			do_action( 'w2m_import_attachment_mkdir_error', $error, $upload );

			return;
		}

		/**
		 * Todo: fetch the remote url and write it directly to the placeholder file
		 * using parameter 'stream' => TRUE and 'filename' => $upload[ 'file' ]
		 */
		$response = $this->http->request( $attachment_url );

		if ( is_wp_error( $response ) || 200 !== (int) $response[ 'response' ][ 'code' ] ) {

			if ( ! is_wp_error( $response ) ) {
				/**
				 * @var array $response {
				 *      array $headers,
				 *      string $body,
				 *      array $response {
				 *          int $code,
				 *          string $message
				 *      }
				 *      array $cookies
				 *      string|NULL $filename
				 * }
				 */
				$response = new WP_Error( 'http_request', 'HTTP request failed', $response );
			}
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $response
			 * @param array $upload
			 */
			do_action( 'w2m_import_request_attachment_error', $response, $attachment_url );

			return;
		}

		// Todo: Can be removed when $this->http->request() call is refactored. See above.
		file_put_contents( $upload[ 'file' ], $response[ 'body' ] );

		/** post_mime_type to the attachment */
		wp_update_post(
			array(
				'ID'             => $attachment_id,
				'post_mime_type' => $filetype[ 'type' ]
			)
		);

		// Generate the metadata for the attachment, and update the database record.
		$attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $upload[ 'file' ] );
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata );

		/**
		 * @param array $upload
		 * @param Type\ImportPostInterface $import_post
		 */
		do_action( 'w2m_attachment_imported', $upload, $import_post );
	}
}