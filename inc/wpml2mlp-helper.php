<?php

class WPML2MLP_Helper {
        
        /**
         * Gets all posts from wp db.
         * @return posts array
         */
	public static function get_all_posts() {

		$query_params = array(
			'posts_per_page' => - 1,
			'post_type'      => get_post_types( array( 'public' => TRUE ), 'names', 'and' )
		);


		return get_posts($query_params);
	}
        
        /**
	 * @param int $blog_id
         * @param string $flag_url
	 * @return bool
	 */
	public static function update_flag( $blog_id, $flag_url ) {
                $flag_url = $flag_url || '';
                
                if( $blog_id > 0 ) {
                    
                        return update_blog_option( $blog_id, 'inpsyde_multilingual_flag_url', $flag_url  || '' );                    
                }
                
                return false;
	}
}