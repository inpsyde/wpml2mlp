<?php

if ( ! class_exists( 'WPML2MLP_Helper' ) ) {
	require plugin_dir_path( __FILE__ ) . 'wpml2mlp-Helper.php';
}

class MLP_Site_Creator {

	/**
	 *
	 * @var site_exists_arr
	 */
	private $site_exists_arr;

	private $wpdb;

	/**
	 * Constructs the MLP_Site_Creator
	 *
	 */
	public function __construct( wpdb $wpdb ) {

		$this->wpdb            = $wpdb;
		$this->site_exists_arr = array();
		$this->populate_installed_languages();

	}

	/**
	 * Checks if the site for the provided language already exists
	 *
	 * @param type $language
	 *
	 * @return boolean
	 */
	public function site_exists( $language ) {

		//if we already checked the language or language is main we don't create new site
		if ( array_key_exists( $language, $this->site_exists_arr ) || $this->is_main_language() == $language ) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Creates new MLP site for the provided language
	 *
	 * @param type $language
	 */
	public function create_site( $language ) {

		$is_multisite_on_subdomain = $this->check_is_subdomain_multisite_running();
		$current_site              = get_current_site();
		$domain                    = $is_multisite_on_subdomain ? $language . $current_site->domain
			: $current_site->domain;
		$path                      = $is_multisite_on_subdomain ? "/" : "/" . $language;
		$user_id                   = get_current_user_id();

		//set this before creating the new blog
		//set correct $_POST params, how we can use wpmu_new_blog filter for writing the correct data
		$this->set_or_update_post_obj( $this->convert_to_lang_obj( $language ) );

		wpmu_create_blog( $domain, $path, strtoupper( $language ) . " site", $user_id );

		$this->site_exists_arr[ $language ] = TRUE;
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
	private function set_or_update_post_obj( $lng_obj ) {

		$_POST[ 'inpsyde_multilingual_lang' ] = $lng_obj;

		//set default blog (check do we need to fetch default from db)
		$_POST[ 'related_blogs' ] = array(
			0 => "1"
		);
	}

	private function convert_to_lang_obj( $language ) {

		$query  = $this->wpdb->prepare(
			"SELECT http_name FROM `wp_mlp_languages` WHERE iso_639_1 = " . "%s LIMIT 1", $language
		);
		$result = $this->wpdb->get_var( $query );

		return NULL === $result ? '' : str_replace( '-', '_', $result );
	}

	private function is_main_language() {

		global $sitepress;
		$main_lng = $sitepress->get_default_language();

		return $main_lng;
	}

	private function populate_installed_languages() {

		$sites = wp_get_sites();
		foreach ( $sites as $site ) {
			$lng                           = get_blog_language( $site[ 'blog_id' ] );
			$this->site_exists_arr[ $lng ] = TRUE;

		}
	}

}