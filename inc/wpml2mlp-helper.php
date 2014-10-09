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
}