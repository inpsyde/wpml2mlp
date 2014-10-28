<?php

/**
 * Class Wpml2mlp_Helper
 */
class Wpml2mlp_Helper {

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
	public static function  get_language_info( $post_id ) {

		global $wpdb;
		$query      = $wpdb->prepare(
			'SELECT language_code FROM ' . $wpdb->prefix . 'icl_translations WHERE element_id="%d"', $post_id
		);
		$query_exec = $wpdb->get_row( $query );

		if ( $query_exec == NULL ) {
			$query      = $wpdb->prepare(
				'SELECT language_code FROM ' . $wpdb->base_prefix . 'icl_translations WHERE element_id="%d"', $post_id
			);
			$query_exec = $wpdb->get_row( $query );

		}
		if ( $query_exec == NULL ) {
			return NULL;

		}

		return $query_exec->language_code;

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

		return (int) icl_object_id( $post->ID, $post->post_type, TRUE, $main_language );
	}
}