<?php

define( 'WPVERSION_CONST', '3.1' );

/**
 * Class Wpml2mlp_Prerequisites
 */
class Wpml2mlp_Prerequisites {

	/**
	 * @var bool
	 */
	private static $prerequisites = TRUE;

	/**
	 *
	 */
	public static function check_prerequisites( $txt_domain, $error_code ) {

		$error = new WP_Error();

		if ( self::check_wordpress_version() ) {

			$msg = sprintf(
				__( '"Wpml2mlp" requires WordPress %1$s or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href="%2$s">WordPress admin</a>.', $txt_domain ),
				2, WPVERSION_CONST, admin_url()
			);

		} elseif ( ! is_multisite() ) {

			$xliff_export = new Wpml_Xliff_Export();
			$xliff_export->setup( 'multisite_not_installed' );

			$msg = sprintf( __( ' Hier wird die neue wpml2mlp info Seite ausgegeben.<br /><br />Back to <a href="%2$s">WordPress admin</a>.', $txt_domain ), 1, admin_url() );

		} elseif ( ! self::is_wpmlplugin_active() ) {

			$msg = sprintf( __( 'Sorry you have to activate the plugin wpml!<br /><br />Back to <a href="%2$s">WordPress admin</a>.', $txt_domain ), 1, admin_url() );

		} else if ( self::is_mlp_plugin_active() ) {

			$msg = sprintf( __( 'Sorry you have to install and activate the plugin Multilingual Press! .<br /><br />Back to <a href="%2$s">WordPress admin</a>.', $txt_domain ), 1, admin_url() );

		}

		if( ! empty( $msg ) ){

			$error->add( $error_code, $msg );

		}

		return $error;

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

		if( ! $act_plugs )
			return;

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
	public static function is_wpmlplugin_active() {

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