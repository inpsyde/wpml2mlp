<?php

class WPML2MLP_Helper {

	public static function get_all_posts() {

		$query_params = array(
			'posts_per_page' => - 1,
			'post_type'      => get_post_types( array( 'public' => TRUE ), 'names', 'and' )
		);


		return get_posts($query_params);
	}
}