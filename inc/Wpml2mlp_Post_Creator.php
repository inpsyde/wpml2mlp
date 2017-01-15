<?php

/**
 * Class Wpml2mlp_Post_Creator
 */
class Wpml2mlp_Post_Creator {

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
	 * Constructs the WPML2MLP_Post_Creator
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
	 * Adds the post to the relevant site
	 *
	 * @param $post
	 *
	 * @param $blog
	 *
	 * @return int|\WP_Error
	 */
	public function add_post( $post, $blog ) {

		if ( ! $blog || $this->post_exists( $post, $blog ) ) {
			return FALSE;
		}

		$original_post_id  = $post->ID; // store temp so that we can return it to the original post
		$source_content_id = Wpml2mlp_Helper::get_default_post_ID( $post );
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
				Wpml2mlp_Helper::get_default_blog(),
				(int) $blog[ 'blog_id' ],
				$source_content_id,
				$new_post_id,
				$post->post_type
			);
		}

		$post->ID = $original_post_id;

		return $new_post_id;
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

		if ( (int) $blog[ 'blog_id' ] == Wpml2mlp_Helper::get_default_blog()
		) { // default site, we don't need to copy that?
			return TRUE;
		}

		return self::get_multisite_id( $post, $blog ) != - 1;
	}

	/**
	 * Gets the relevant multisite post id from singlesite post.
	 *
	 * @param $post
	 * @param $blog
	 *
	 * @return int
	 */
	public function get_multisite_id( $post, $blog ) {

		$rel = $this->content_relations->get_relations(
			Wpml2mlp_Helper::get_default_blog(), Wpml2mlp_Helper::get_default_post_ID( $post ), $post->post_type
		);

		$blog_id = (int) $blog[ 'blog_id' ];

		$multisite_id = - 1;
		foreach ( $rel as $key => $value ) {
			if ( $key == $blog_id ) {
				$multisite_id = $value;
				break;
			}
		}

		return $multisite_id;
	}

	/**
	 * Updates the post on the relevant language
	 *
	 * @param $post
	 *
	 * @param $blog
	 *
	 * @return int|\WP_Error
	 */
	public function update( $post, $blog ) {

		if ( ! $blog || ! self::post_exists( $post, $blog ) ) {
			return FALSE;
		}

		$multisite_post_id = self::get_multisite_id( $post, $blog );

		switch_to_blog( (int) $blog[ 'blog_id' ] );

		$multisite_post = get_post( $multisite_post_id );

		if ( self::update_post_content( $post, $multisite_post ) ) {
			wp_update_post( $multisite_post );
		}

		restore_current_blog();

		return TRUE;
	}

	/**
	 * Updates the multisite post with new content.
	 *
	 * @param $post
	 * @param $multisite_post
	 *
	 * @return bool
	 */
	private function update_post_content( $post, &$multisite_post ) {

		$ret = FALSE;

		if ( $post->post_title != $multisite_post->post_title ) {
			$multisite_post->post_title = $post->post_title;
			$ret                        = TRUE;
		}

		if ( $post->post_content != $multisite_post->post_content ) {
			$multisite_post->post_content = $post->post_content;
			$ret                          = TRUE;
		}

		return $ret;
	}
}