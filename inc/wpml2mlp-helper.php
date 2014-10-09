<?php

class WPML2MLP_Helper {

	public static function get_all_posts() {

		$queryParams = array(
			'posts_per_page' => - 1,
			'post_type'      => get_post_types( array( 'public' => TRUE ), 'names', 'and' )
		);
		$ret         = new WP_Query();
		$ret->query( $queryParams );

		return $ret;
	}

	public static function append_and_update_site_meta( &$site_meta_arr, $lng, $new_blog_id, $text = "" ) {

		$new_meta =
			array(
				'lang' => $lng,
				'text' => $text,
			);

		$site_meta_arr[ $new_blog_id ] = $new_meta;

		update_site_option( "inpsyde_multilingual", serialize( $site_meta_arr ) );
	}
}