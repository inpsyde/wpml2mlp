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
    
        /**
	 * Constructs the MLP_Site_Creator
	 *
	 */
	public function __construct() { 
                $this->site_exists_arr = array();
	}
        
        /**
         * Checks if the site for the provided language already exists
         * @param type $language
         * @return boolean
         */
        public function site_exists( $language ) {
                if ( array_key_exists( $language, $this->site_exists_arr ) ) {
                        return TRUE;
                }
                
                // TODO: check here does site exists
                
                return TRUE;
        }
        
        /**
         * Creates new MLP site for the provided language
         * @param type $language
         */
        public function create_site( $language ) {
                $is_multisite_on_subdomain = self::check_is_subdomain_multisite_running();
		$current_site              = get_current_site();
		$domain                    = $is_multisite_on_subdomain ? $lng . $current_site->domain : $current_site->domain;
		$path                      = $is_multisite_on_subdomain ? "/" : "/" . $lng;
		$user_id                   = get_current_user_id();

		$new_blog_id = wpmu_create_blog( $domain, $path, strtoupper( $language ) . " site", $user_id );
                
                $site_meta_arr = maybe_unserialize( get_site_option( "inpsyde_multilingual" ) );
                
                WPML2MLP_Helper::append_and_update_site_meta( $site_meta_arr, $language, $new_blog_id );
                
                $this->site_exists_arr[ $language ] = TRUE; 
        }
        
        /**
         * Checks how the multisite is configured (subdomain or folder separation)
         * @return type
         */
        private function check_is_subdomain_multisite_running() {

		return defined( 'SUBDOMAIN_INSTALL' ) ? SUBDOMAIN_INSTALL : FALSE;
	}
    
}