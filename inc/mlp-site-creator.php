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
	 *
	 * @var Mlp_Language_Api
	 */
	private $language_api;

	/**
	 *
	 * @var Mlp_Network_New_Site_Controller
	 */
	private $network_new_site_controler;

	/**
	 * Constructs the MLP_Site_Creator
	 *
	 */
	public function __construct(
		wpdb $wpdb,
		Mlp_Site_Relations_Interface $site_relations,
		Mlp_Content_Relations_Interface $content_relations
	) {

		if ( NULL == $wpdb ) {
			return;
		}

		$this->wpdb         = $wpdb;
		$this->language_api = new Mlp_Language_Api(
			new Inpsyde_Property_List,
			'mlp_languages',
			$site_relations,
			$content_relations,
			$wpdb
		);

		$this->network_new_site_controler = new Mlp_Network_New_Site_Controller(
			$this->language_api,
			$site_relations
		);
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

		if ( ! $ret && get_current_site()->id == $lng[ 'id' ] ) {
			$ret = TRUE; // it is default, do we need to create?
		}

		return $ret;
	}

	/**
	 * Creates new MLP site for the provided language
	 *
	 * @param type $language
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
			$this->network_new_site_controler->update( $blog_id );
			WPML2MLP_Helper::update_flag( $blog_id, $language[ 'country_flag_url' ] );
		}

		return $blog_id;
	}

	/**
	 *
	 * @param int    $blog_id
	 * @param string $language
	 *
	 * @return void
	 */
	public function check_and_update_site_lagnguage( $blog_id, $language ) {

		$languages = (array) get_site_option( 'inpsyde_multilingual', array() );

		if ( empty ( $languages[ $blog_id ] ) ) {
			$languages[ $blog_id ] = array();
		} else {
			return;
		}

		$languages[ $blog_id ][ 'lang' ] = str_replace( '-', '_', $language );

		update_site_option( 'inpsyde_multilingual', $languages );
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
		$_POST[ 'inpsyde_multilingual_lang' ]     = $language[ 'default_locale' ];
		$_POST[ 'related_blogs' ]                 = array( $current_site->id );
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