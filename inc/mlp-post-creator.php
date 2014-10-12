<?php

if ( ! class_exists( 'WPML2MLP_Helper' ) ) {
	require plugin_dir_path( __FILE__ ) . 'wpml2mlp-Helper.php';
}

class MLP_Post_Creator {
    
        /**
         *
         * @var wpdb
         */
        private $wpdb;

        /**
         *
         * @var Mlp_Content_Relations_Interface 
         */
        private $content_relations;

	/**
	 * Constructs the MLP_Post_Creator
	 *
	 */
	public function __construct(
                wpdb $wpdb,
                Mlp_Content_Relations_Interface $content_relations) {
            
                if ( null == $wpdb ) {
                    return;
                }
                
                $this->wpdb = $wpdb;
                $this->content_relations = $content_relations;
	}

	/**
	 * Checks does the post already exists.
	 *
	 * @param $post
         * 
         * @param $blog
	 *
	 * @return boolean
	 */
	public function post_exists( $post, $blog ) {
                if( (int)$blog[ 'blog_id' ] == get_current_blog_id() ) { // default site, we don't need to copy that?
                    return TRUE;
                }
            
                $rel = $this->content_relations->get_relations( (int)$blog[ 'blog_id' ], $post->ID, $post->post_type);
                
		return !empty($rel);
	}

	/**
	 * Adds the post to the relevant language
	 *
	 * @param $post
         * 
         * @param $blog
	 */
	public function add_post( $post, $blog ) {
                if ( !$blog || $this->post_exists($post, $blog) ) {
                        return;
                }
                $source_content_id = (int)$post->ID;
                
                $post->ID = null; // reset the post_id, new one will be created
                
                switch_to_blog( (int)$blog[ 'blog_id' ] );
                $new_post_id = wp_insert_post( (array)$post );
                restore_current_blog();
                
                if ( 0 < $new_post_id ) {
                        $this->content_relations->set_relation( 
                                get_current_blog_id(), 
                                (int)$blog['blog_id'], 
                                $source_content_id, 
                                $new_post_id, 
                                $post->post_type);
                }
	}
}