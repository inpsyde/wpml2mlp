<?php # -*- coding: utf-8 -*-

namespace W2M\System;

/**
 * Class ImportEnvironment
 *
 * @package W2M\System
 */
class ImportEnvironment {

	/**
	 * @return bool
	 */
	public function is_multisite() {

		return is_multisite();
	}

	/**
	 * @return bool
	 */
	public function mlp_is_active() {

		$mlp_language_api = apply_filters( 'mlp_language_api', NULL );

		return is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' );
	}

	/**
	 * @param $blog_id
	 *
	 * @return string
	 */
	public function mlp_blog_language( $blog_id ) {

		if ( ! function_exists( 'mlp_get_blog_language' ) )
			return '';

		return mlp_get_blog_language( $blog_id, FALSE );
	}
}