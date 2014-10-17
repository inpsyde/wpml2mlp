<?php

class WPML2MLP_Helper {

	/**
	 * Gets all posts from wp db.
	 *
	 * @return posts array
	 */
	public static function get_all_posts() {

		$query_params = array(
			'posts_per_page' => - 1,
			'post_type'      => get_post_types( array( 'public' => TRUE ), 'names', 'and' )
		);

		return get_posts( $query_params );
	}

	/**
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

	public static function get_short_language( $language ) {

		if ( empty( $language ) ) {
			return "";
		}

		return substr( $language, 0, 2 );
	}

	public static function  get_language_info( $post_id ) {

		return wpml_get_language_information( $post_id );
	}

	/**
	 * Get default blog
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

		global $sitepress;
		$main_lng = $sitepress->get_default_language();

		return $main_lng;
	}
}