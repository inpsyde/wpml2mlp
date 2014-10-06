<?php
/**
 * Plugin Name: WPML 2 MLP
 * Plugin URI:  http://marketpress.com/product/multilingual-press-pro/?piwik_campaign=mlp&piwik_kwd=pro
 * Description: Get data from WPML export and immediately import in Multisite environment .
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     1.0.0
 * Network:     true
 */

defined( 'ABSPATH' ) or die();

define( "WPVERSION_CONST", "3.1" );

class Wpml_2_Mlp {

	function check_prerequisites() {

		$wp_version_check      = $this->check_wordpress_version();
		$wp_is_multisite_check = $this->check_is_multisite_enabled();

		if ( $wp_version_check || $wp_is_multisite_check ) {
			$plugin      = plugin_basename( __FILE__ );
			$plugin_data = get_plugin_data( __FILE__, FALSE );
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				if ( $wp_version_check ) {

					$msg = "'" . $plugin_data[ 'Name' ] . "' requires WordPress " . WPVERSION_CONST . " or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url(
						) . "'>WordPress admin</a>.";
				} else {
					$msg = "Multisite needs to be enabled";
				}

				wp_die( $msg );
			}
		}

	}

	function check_is_multisite_enabled() {

		return ! is_multisite() ? TRUE : FALSE;
	}

	function check_wordpress_version() {

		global $wp_version;

		return version_compare( $wp_version, WPVERSION_CONST, "<" ) ? TRUE : FALSE;
	}

	function __construct() {

		//TODO Check do we need version test!
		add_action( "admin_init", array( &$this, "check_prerequisites" ) );

		// add menu to navigation
		add_action( "admin_menu", array( &$this, "add_menu_option" ) );

	}

	// Add menu page
	function add_menu_option() {

		global $wpml2mlp;

		$wpml2mlp = add_options_page(
			'Convert WPML to MLP', 'WPML2MLP', 'manage_options', 'wpmltomlp', array( &$this, 'options_page' )
		);

		add_action( 'load-' . $wpml2mlp, array( &$this, 'contextual_help_tab' ) );

	}
}

//init plugin
add_action( "init", "wpml_2_mlp_init" );

function wpml_2_mlp_init() {

	global $wpml_2_mlp;
	$wpml_2_mlp = new Wpml_2_Mlp();
}