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
		Mlp_Content_Relations_Interface $content_relations
	) {

		if ( NULL == $wpdb ) {
			return;
		}

		$this->wpdb              = $wpdb;
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

		if ( (int) $blog[ 'blog_id' ] == WPML2MLP_Helper::get_default_blog()
		) { // default site, we don't need to copy that?
			return TRUE;
		}

		$rel = $this->content_relations->get_relations( (int) $blog[ 'blog_id' ], $post->ID, $post->post_type );

		return ! empty( $rel );
	}

	/**
	 * Adds the post to the relevant language
	 *
	 * @param $post
	 *
	 * @param $blog
	 */
	public function add_post( $post, $blog ) {

		if ( ! $blog || $this->post_exists( $post, $blog ) ) {
			return FALSE;
		}

		$original_post_id  = $post->ID; // store temp so that we can return it to the original post
		$source_content_id = (int) icl_object_id( $post->ID, $post->post_type, TRUE );
		$meta              = get_post_meta( $post->ID );
		$post->ID          = NULL; // reset the post_id, new one will be created

		switch_to_blog( (int) $blog[ 'blog_id' ] );
		$new_post_id = wp_insert_post( (array) $post );
		if ( $new_post_id > 0 ) {
			foreach ( $meta as $key => $value ) {
				update_post_meta( $new_post_id, $key, $value[ 0 ] );
			}
		}

		restore_current_blog();

		if ( 0 < $new_post_id ) {
			$this->content_relations->set_relation(
				WPML2MLP_Helper::get_default_blog(),
				(int) $blog[ 'blog_id' ],
				$source_content_id,
				$new_post_id,
				$post->post_type
			);
		}

		$post->ID = $original_post_id;

		return $new_post_id;
	}
}