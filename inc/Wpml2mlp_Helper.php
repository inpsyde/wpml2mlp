<?php

/**
 * Class Wpml2mlp_Helper
 */
class Wpml2mlp_Helper {

	public static $next_job = FALSE;

	/**
	 * Gets all posts from wp db.
	 *
	 * @return posts array
	 */
	public static function get_all_posts() {

		global $sitepress;

		$term_types = apply_filters( 'wpml2mlp_export_supported_terms', array( 'category', 'post_tag' ) );

		$posttypes = array(
			'post' => 'post',
			'page' => 'page'
		);

		$query_params = array(
			'posts_per_page' => -1,
			'post_type'      => apply_filters( 'wpml2mlp_export_supported_posttypes', $posttypes ),
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' )

		);

		$languages = FALSE;
		if ( array_key_exists( 'wpml2mlp', $_GET ) &&
		     array_key_exists( 'languages', $_GET ) ||
		     array_key_exists( 'wpml2mlp', $_GET ) &&
		     array_key_exists( 'language', $_GET )
		) {

			if ( $_GET[ 'wpml2mlp' ] == 'nextjob' && ! empty( $_GET[ 'nextjob' ] ) ) {

				$languages = array_flip( explode( ',', $_GET[ 'languages' ] ) );
				$jobs_done = explode( ',', $_GET[ 'jobsdone' ] );

			} elseif( ! empty( $_GET[ 'language' ] ) ){

				$languages[ $_GET[ 'language' ] ] = $_GET[ 'language' ];
				$lang_data[ 'default_locale' ] = $_GET[ 'language' ];

			}else {

				foreach( $_GET[ 'languages' ] as $language ){
					$languages[ $language ] = $language;
				}

			}

		} else {

			$languages = wpml_get_active_languages_filter( 1 );


		}

		$jobs_done = [ ];
		$lang = [ ];

		$attachments               = [ ];
		$attachments_flipped_index = [ ];


		foreach ( $languages as $lang_code => $lang_data ) {

			$lang[] = $lang_code;
		}

		$all_posts = array();

		foreach ( $languages as $lang_code => $lang_data ) {

			if( ! array_key_exists( 'language', $_GET ) ){
					self::set_next_job( [ $lang_code, $lang, $jobs_done ] );
			}

			$current_lang_info = wpml_get_active_languages_filter( 1 );
			$current_lang_info = $current_lang_info[ $lang_code ];

			$sitepress->switch_lang( $lang_code );


			$query = new WP_Query( $query_params );

			if ( ! empty( $query->posts ) ) {

				foreach ( $query->posts as $i => $post ) {

					#$media = get_attached_media( 'image', $post->ID );

					if ( $post->post_type == 'attachment' ) {

						$attachments_flipped_index[] = $post;

					} else {

						$media = get_attached_media( 'image', $post->ID );

						# get used tumbnail
						$thumbnail_id               = get_post_thumbnail_id( $post );
						$thumbnail[ $thumbnail_id ] = get_post( $thumbnail_id );

						$thumbnail = array_filter( $thumbnail );

						# get used in post
						preg_match_all( '/attachment_([0-9]+)"/', $post->post_content, $match );

						$attachments_used = [ ];

						if ( ! empty( $match[ 1 ] ) ) {

							foreach ( $match[ 1 ] as $attachment_used ) {

								$attachments_used[] = get_post( $attachment_used );

							}
						}

						$attachments = array_merge( $media, $thumbnail );
						$attachments = array_merge( $attachments, $attachments_used );

						$attachments_flipped_index = [ ];

						foreach ( $attachments as $attachment ) {

							if ( is_object( $attachment ) && property_exists( $attachment, 'ID' ) ) {
								$attachments_flipped_index[ $attachment->ID ] = $attachment;
							}

						}

						$attachments = $attachments_flipped_index;

						$all_posts[ $current_lang_info[ 'default_locale' ] ][ 'posts' ] = $query->posts;

						foreach ( $term_types as $term ) {

							if ( $post->post_type != 'attachment' ) {

								$terms = wp_get_object_terms( $post->ID, $term );


								if ( ! empty( $terms ) ) {

									foreach ( $terms as $t ) {

										$all_posts[ $current_lang_info[ 'default_locale' ] ][ $term ][ $t->term_id ] = $t;

									}

								}

							}

						}


						$all_posts[ $current_lang_info[ 'default_locale' ] ] = apply_filters( 'wpml2mlp_export_terms', $all_posts[ $current_lang_info[ 'default_locale' ] ] );

					}

					$posts = array_merge( $attachments, $query->posts );

					$all_posts[ $current_lang_info[ 'default_locale' ] ][ 'posts' ] = $posts;

				}

			}else{


				if( array_key_exists( 'action', $_GET ) ){

					print_r( json_encode( [
						                   'filesize' => 0,
						                   'date' =>  date( 'm.d.y H:i', time() )
					                   ]
							)
					);
					die();

				}else{

					wp_redirect( network_admin_url() . '?wpml2mlp=jobsalldone&jobs=' . implode( ',', $jobs_done ) );

				}


			}

			#save memory claenup the $query
			unset( $query );
			unset( $posts );
			unset( $attachments );
			unset( $attachments_flipped_index );
			unset( $terms );
			#unset( $current_lang_info);

		}

		if ( ! empty( $all_posts ) ) {

			return $all_posts;

		}


	}

	private static function set_next_job( $job_items ) {

		$jobs_done = $job_items[2];
		$languages = array_flip( $job_items[1] );

		unset( $languages[$job_items[0]] );

		$languages = array_merge( array_flip( $languages ) );

		$next_job = $languages[0];

		#unset( $languages[0] );

		$jobs_done[] = $job_items[0];

		self::$next_job = [
			'current_lang' => $job_items[0],
			'next_lang'    => $next_job,
			'languages'    => $languages,
			'jobs_done'    => $jobs_done
		];

	}

	public static function get_next_job() {

		return self::$next_job;

	}

	public function get_current_lang_group() {

		if ( FALSE === ( $lang = get_transient( 'wpml2mlp_languages' ) ) ) {

			$languages = wpml_get_active_languages_filter( FALSE );

			$count      = 0;
			$chunk      = 0;
			$chunk_size = 1;

			foreach ( $languages as $lang_code => $language ) {
				if ( $count > $chunk_size ) {
					$chunk ++;
					$count = 0;
				}
				$lang[ $chunk ][ $lang_code ] = $language;
				$count ++;
			}

		}

		if( count( $lang ) == 0 ){

			delete_transient( 'wpml2mlp_languages' );

			die( 'no more languages found' );

		}else{

			$languages = array_shift( $lang );

			set_transient( 'wpml2mlp_languages', $lang, WEEK_IN_SECONDS );

		}

		return $languages;

	}

	/**
	 * Updates flag url for given blog.
	 *
	 * @param int    $blog_id
	 * @param string $flag_url
	 *
	 * @return bool
	 */
	public static function update_flag( $blog_id, $flag_url ) {

		$flag_url = empty( $flag_url ) ? '' : $flag_url;

		if ( $blog_id > 0 ) {

			return update_blog_option( $blog_id, 'inpsyde_multilingual_flag_url', $flag_url );
		}

		return FALSE;
	}

	/**
	 * Determinate is main language
	 *
	 * @param $lng
	 *
	 * @return bool
	 */
	public static function is_main_language( $lng ) {

		$ret = FALSE;
		if ( is_array( $lng ) && array_key_exists( 'language_code', $lng ) ) {

			$ret = self::get_main_language() == $lng[ 'language_code' ] ? TRUE : FALSE;
		}

		return $ret;
	}

	/**
	 * Convert language to mlp culture. ie: hr_HR
	 *
	 * @param wpdb $wpdb
	 * @param      $language
	 *
	 * @return mixed|string
	 */
	public static function convert_to_mlp_lang_obj( wpdb $wpdb, $language ) {

		$query  = $wpdb->prepare(
			"SELECT http_name FROM `wp_mlp_languages` WHERE iso_639_1 = " . "%s LIMIT 1", $language
		);
		$result = $wpdb->get_var( $query );

		return NULL === $result ? $language : str_replace( '-', '_', $result );
	}

	/**
	 * Gets the short language code for given culture.
	 *
	 * @param $language
	 *
	 * @return string
	 */
	public static function get_short_language( $language ) {

		if ( empty( $language ) ) {
			return "";
		}

		return substr( $language, 0, 2 );
	}

	/**
	 * Gets the language info for the given post id.
	 *
	 * @param $post_id
	 *
	 * @return null
	 */
	public static function get_language_info( $post_id ) {

		$language_code = wpml_get_language_information( FALSE, $post_id );

		return $language_code[ 'language_code' ];

	}

	/**
	 * Gets default blog.
	 *
	 * @return int
	 */
	public static function get_default_blog() {

		$ret   = 1;
		$sites = wp_get_sites();
		if ( $sites != NULL && is_array( $sites ) && count( $sites ) > 0 ) {
			$ret = $sites[ 0 ][ 'blog_id' ];
		}

		return $ret;
	}

	/**
	 * Get main language from database
	 *
	 * @return string
	 */
	public static function get_main_language() {

		if ( is_multisite() ) {
			$settings = get_blog_option( self::get_default_blog(), 'icl_sitepress_settings', - 1 );
		} else {
			$settings = get_option( 'icl_sitepress_settings', - 1 );
		}

		return isset( $settings[ 'default_language' ] ) ? $settings[ 'default_language' ] : FALSE;
	}

	/**
	 * Gets the main post id from multisite language post.
	 *
	 * @param $post
	 *
	 * @return int
	 *
	 */
	public static function get_default_post_ID( $post ) {

		$main_language = Wpml2mlp_Helper::get_main_language();

		return (int) wpml_object_id_filter( $post->ID, $post->post_type, TRUE, $main_language );

	}
}