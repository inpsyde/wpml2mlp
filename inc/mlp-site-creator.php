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
         * @var Mlp_Site_Relations 
         */
        private $site_relations;
        
        /**
         *
         * @var Mlp_Content_Relations 
         */
        private $content_relations;
        
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
                wpdb $wpdb ) {
            
                if ( null == $wpdb ) {
                    return;
                }
                
		$link_table             = $wpdb->base_prefix . 'multilingual_linked';  
                $this->wpdb = $wpdb;
                $this->site_relations = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
                $this->content_relations = new Mlp_Content_Relations(
			$wpdb,
			$this->site_relations,
			$link_table);
                
                $this->language_api = new Mlp_Language_Api(
			new Inpsyde_Property_List,
			'mlp_languages',
			$this->site_relations,
			$this->content_relations,
			$wpdb
		);
                
                $this->network_new_site_controler = new Mlp_Network_New_Site_Controller(
                        $this->language_api,
                        $this->site_relations );
	}

	/**
	 * Checks if the site for the provided language already exists
	 *
	 * @param type $language
	 *
	 * @return boolean
	 */
	public function site_exists( $lng ) {
                $ret = false;
                
                if ( $this->language_exists( $lng ) ) {
			$ret = true; // already exists mlp site
		}
                
                if ( ! $ret && get_current_site()->id == $lng['id'] ) {
                        $ret = true; // it is default, do we need to create?
                }
                
		return $ret;
	}

	/**
	 * Creates new MLP site for the provided language
	 *
	 * @param type $language
	 */
        public function create_site( $language ) {
                if ( $this->site_exists( $language ) ) {
                    return false;
                }
                
                $active                    = (int) $language['active'];
                $lng_code                  = $language['language_code'];
		$is_multisite_on_subdomain = $this->check_is_subdomain_multisite_running();
		$current_site              = get_current_site();
		$domain                    = $is_multisite_on_subdomain ? $lng_code . $current_site->domain : $current_site->domain;
		$path                      = $is_multisite_on_subdomain ? "/" : "/" . $lng_code;
		$user_id                   = get_current_user_id();
                
		$this->set_or_update_post_obj( $language, $current_site  );
                
		$blog_id = wpmu_create_blog( 
                        $domain, 
                        $path, 
                        "My " . $language['translated_name'] . " site", 
                        $user_id, 
                        array( 'public' => $active ), 
                        $current_site->id );
                
                if ( 0 < $blog_id ) {
                    $this->network_new_site_controler->update($blog_id);
                }
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
		$_POST[ 'inpsyde_multilingual_lang' ] = $language['default_locale'];
		$_POST[ 'related_blogs' ] = array( $current_site->id );
	}
        
        private function language_exists( $language ) {
                $all_lngs = mlp_get_available_languages();   
                
                foreach ( $all_lngs as $lng ) {
                        if ( $language['language_code'] == $lng ||
                             $language['default_locale'] == $lng ) {
                                return true;
                        }
                }
                
                return false;
        }

}