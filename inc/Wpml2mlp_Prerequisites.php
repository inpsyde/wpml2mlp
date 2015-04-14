<?php

define( "WPVERSION_CONST", "3.1" );

/**
 * Class Wpml2mlp_Prerequisites
 */
class Wpml2mlp_Prerequisites {

	public static function check_prerequisites() {

		$plugin_data      = get_plugin_data( __FILE__, FALSE );
		$wp_version_check = self::check_wordpress_version();
		$wpml_installed   = self::is_wpmlplugin_active();

		$die = FALSE;
		if ( $wp_version_check ) {

			$msg = "'" . $plugin_data[ 'Name' ] . "' requires WordPress " . WPVERSION_CONST . " or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url(
				) . "'>WordPress admin</a>.";
			$die = TRUE;
		}

		if ( ! is_multisite() ) {
			$msg = "Multisite needs to be enabled";
			$die = TRUE;
		}

		if ( ! $wpml_installed ) {
			$msg = "WPML Plugin is not installed or it's not activated";
			$die = TRUE;
		}

		if ( $die ) {
			wp_die( $msg );
		}
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

		return is_plugin_active( 'multilingual-press-pro/multilingual-press.php' ) ? TRUE : FALSE;
	}

	/**
	 * Checks is WP version ok for plugin.
	 *
	 * @return bool
	 */
	private static function check_wordpress_version() {

		global $wp_version;

		return version_compare( $wp_version, WPVERSION_CONST, "<" ) ? TRUE : FALSE;
	}

	/**
	 * Checks is wpml plugin active.
	 *
	 * @return bool
	 */
	private static function is_wpmlplugin_active() {

		return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ? TRUE : FALSE;
	}

}