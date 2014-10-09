<?php

if ( ! class_exists( 'WPML2MLP_Helper' ) ) {
	require plugin_dir_path( __FILE__ ) . 'wpml2mlp-Helper.php';
}

class MLP_Post_Creator {
    
        /**
	 * Constructs the MLP_Post_Creator
	 *
	 */
	public function __construct() { 
            
	}
        
        /**
         * Checks does the post already exists.
         * @param type $translation
         * @return boolean
         */
        public function post_exists( $translation ) {
            
                // TODO: implement this properly
                return TRUE;
        }
        
        /**
         * Adds the post to the relevant language
         * @param type $translation
         */
        public function add_post ( $translation ) {
            
            // TODO: implement this properly
        }
    
}