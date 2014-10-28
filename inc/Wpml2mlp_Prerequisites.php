<?php

define( "WPVERSION_CONST", "3.1" );

/**
 * Class Wpml2mlp_Prerequisites
 */
class Wpml2mlp_Prerequisites {

	public static function check_prerequisites() {

		$wp_version_check = self::check_wordpress_version();
		$wpml_installed   = self::is_wpmlplugin_active();

		if ( $wp_version_check || ! $wpml_installed ) {
			$plugin      = plugin_basename( __FILE__ );
			$plugin_data = get_plugin_data( __FILE__, FALSE );
			if ( is_plugin_active( $plugin ) ) {

				deactivate_plugins( $plugin );

				if ( $wp_version_check ) {

					$msg = "'" . $plugin_data[ 'Name' ] . "' requires WordPress " . WPVERSION_CONST . " or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url(
						) . "'>WordPress admin</a>.";
				}
				$msg = '';
				if ( $wp_is_multisite_check ) {
					$msg = "Multisite needs to be enabled";
				}

				if ( ! $wpml_installed ) {
					$msg = "WPML Plugin is not installed or it's not activated";
				}

				wp_die( $msg );
			}
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