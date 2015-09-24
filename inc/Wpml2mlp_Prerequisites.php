<?php

define( 'WPVERSION_CONST', '3.1' );

/**
 * Class Wpml2mlp_Prerequisites
 */
class Wpml2mlp_Prerequisites {

	public static function check_prerequisites() {

		$wp_version_check = self::check_wordpress_version();
		$wpml_installed   = self::is_wpmlplugin_active();

		$msg = '';
		$die = FALSE;
		if ( $wp_version_check ) {
			$msg = __(
				'"Wpml2mlp" requires WordPress %1$s or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href="%2$s">WordPress admin</a>.',
				'wpml2mlp'
			);
			$url = esc_url( admin_url() );
			$msg = sprintf(
				$msg,
				WPVERSION_CONST,
				$url
			);

			$die = TRUE;
		}

		if ( ! is_multisite() || ! $wpml_installed ) {
			$msg = __(
				'Please ensure, that you have set up a multisite environment and activated WPML.<br /><br />Back to <a href="%s">WordPress admin</a>.',
				'wpml2mlp'
			);
			$url = esc_url( admin_url() );
			$msg = sprintf(
				$msg,
				$url
			);

			$die = TRUE;
		}

		if ( $die ) {
			$plug_basename = plugin_basename( __FILE__ );
			$basename_array = explode( '/', $plug_basename );

			deactivate_plugins( $basename_array[ 0 ] . '/wpml2mlp.php' );
			wp_die( $msg );
		}
	}

	/**
	 * Checks is WP version ok for plugin.
	 *
	 * @return bool
	 */
	private static function check_wordpress_version() {

		global $wp_version;

		return version_compare( $wp_version, WPVERSION_CONST, '<' ) ? TRUE : FALSE;
	}

	/**
	 * Checks if the multisite enabled.
	 *
	 * @return bool
	 */
	public static function is_multisite_enabled() {

		return is_multisite() ? TRUE : FALSE;
	}

	/**
	 * Checks is the mlp plugin active.
	 *
	 * @return bool
	 */
	public static function is_mlp_plugin_active() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		$act_plugs = get_site_option( 'active_sitewide_plugins' );
		$plugs     = array();

		foreach ( $act_plugs as $key => $value ) {
			$plugin_name = explode( '/', $key );
			if ( array_key_exists( 1, $plugin_name ) ) {
				$plugs[] = $plugin_name[ 1 ];
			}
		}

		return in_array( 'multilingual-press.php', $plugs, FALSE );
	}

	/**
	 * Checks is wpml plugin active.
	 *
	 * @return bool
	 */
	private static function is_wpmlplugin_active() {

		$act_plugs = get_option( 'active_plugins' );
		$plugs     = array();

		foreach ( $act_plugs as $key => $value ) {
			$plugin_name = explode( '/', $value );
			if ( array_key_exists( 1, $plugin_name ) ) {
				$plugs[] = $plugin_name[ 1 ];
			}
		}

		return in_array( 'sitepress.php', $plugs, FALSE );
	}
}