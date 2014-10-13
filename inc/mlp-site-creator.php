<?php

if ( ! class_exists( 'WPML2MLP_Helper' ) ) {
	require plugin_dir_path( __FILE__ ) . 'wpml2mlp-Helper.php';
}

class MLP_Site_Creator {

	/**
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Constructs the MLP_Site_Creator
	 *
	 */
	public function __construct(
		wpdb $wpdb
	) {

		if ( NULL == $wpdb ) {
			return;
		}

		$this->wpdb = $wpdb;

	}

	/**
	 * Checks if the site for the provided language already exists
	 *
	 * @param string $language
	 *
	 * @return boolean
	 */
	public function site_exists( $lng ) {

		$ret = FALSE;

		if ( $this->language_exists( $lng ) ) {
			$ret = TRUE; // already exists mlp site
		}

		if ( ! $ret && WPML2MLP_Helper::is_main_language( $lng ) ) {
			$ret = TRUE; // it is default, do we need to create?
		}

		return $ret;
	}

	/**
	 * Creates new MLP site for the provided language
	 *
	 * @param string $language
	 *
	 * @return int
	 */
	public function create_site( $language ) {

		if ( $this->site_exists( $language ) ) {
			return - 1;
		}

		$active                    = (int) $language[ 'active' ];
		$lng_code                  = $language[ 'language_code' ];
		$is_multisite_on_subdomain = $this->check_is_subdomain_multisite_running();
		$current_site              = get_current_site();
		$domain                    = $is_multisite_on_subdomain ? $lng_code . $current_site->domain
			: $current_site->domain;
		$path                      = $is_multisite_on_subdomain ? "/" : "/" . $lng_code;
		$user_id                   = get_current_user_id();

		$this->set_or_update_post_obj( $language, $current_site );

		$blog_id = wpmu_create_blog(
			$domain,
			$path,
			"My " . $language[ 'translated_name' ] . " site",
			$user_id,
			array( 'public' => $active, 'lang_id' => $language[ 'id' ] ),
			$current_site->id
		);

		if ( 0 < $blog_id ) {
			$this->set_after_blog_created_vars( $language, $current_site, $blog_id );
			//$this->network_new_site_controler->update( $blog_id );
			WPML2MLP_Helper::update_flag( $blog_id, $language[ 'country_flag_url' ] );
		}

		return $blog_id;
	}

	/**
	 * Checks how the multisite is configured (subdomain or folder separation)
	 *
	 * @return bool
	 */
	private function check_is_subdomain_multisite_running() {

		return defined( 'SUBDOMAIN_INSTALL' ) ? SUBDOMAIN_INSTALL : FALSE;
	}

	/**
	 * Set global POST object how we can use wpmu_new_blog action and do correct site_options update and relations
	 *
	 * @param $lng_obj
	 *
	 * @return void
	 */
	private function set_or_update_post_obj( $language, $current_site ) {

		$_POST[ 'inpsyde_multilingual_flag_url' ] = $language[ 'country_flag_url' ];
		$_POST[ 'inpsyde_multilingual_lang' ]     = WPML2MLP_Helper::convert_to_mlp_lang_obj(
			$this->wpdb,
			$language[ 'language_code' ]
		);
		$_POST[ 'related_blogs' ]                 = array(
			0 => WPML2MLP_Helper::get_default_blog()
		);
	}

	private function set_after_blog_created_vars( $language, $current_site, $blog_id ) {

		$_POST[ 'id' ] = $blog_id;
	}

	private function language_exists( $language ) {

		$all_lngs = mlp_get_available_languages();

		foreach ( $all_lngs as $lng ) {
			if ( $language[ 'language_code' ] == $lng
				|| $language[ 'default_locale' ] == $lng
			) {
				return TRUE;
			}
		}

		return FALSE;
	}

}