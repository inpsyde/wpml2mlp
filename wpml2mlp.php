<?php
/**
 * Plugin Name: WPML 2 MLP
 * Plugin URI:  http://marketpress.com/product/multilingual-press-pro/?piwik_campaign=mlp&piwik_kwd=pro
 * Description: Get data from WPML export and immediately import in Multisite environment .
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     0.1 Beta
 * Network:     true
 */

defined( 'ABSPATH' ) or die( "No direct access!" );

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! is_plugin_active_for_network( 'multilingual-press-pro/multilingual-press.php' ) && ! is_plugin_active_for_network( 'multilingual-press/multilingual-press.php' ) ) {
	add_action( 'wp_loaded', 'load_wpml2xliff_export' );
}else{

	add_action( 'mlp_and_wp_loaded', 'mlp_and_wp_loaded_handler' );
}

function load_wpml2xliff_export() {

	$class_mappings = array(
		'Wpml_Xliff_Export'             => 'Wpml_Xliff_Export.php',
		'Wpml2mlp_Helper'               => 'Wpml2mlp_Helper.php',
		'Wpml2mlp_Xliff_Creator'        => 'Wpml2mlp_Xliff_Creator.php',
		'Wpml2mlp_ZipCreator'           => 'Wpml2mlp_ZipCreator.php',
		'Wpml2mlp_Translation_Item'     => 'Wpml2mlp_Translation_Item.php',
		'Wpml2mlp_Language_Holder'      => 'Wpml2mlp_Language_Holder.php',
		'Wpml2mlp_Translations_Builder' => 'Wpml2mlp_Translations_Builder.php',
		'Wpml2mlp_Translations'         => 'Wpml2mlp_Translations.php',
		'Wpml2mlp_Prerequisites'        => 'Wpml2mlp_Prerequisites.php'
	);

	foreach ( $class_mappings as $key => $value ) {
		if ( ! class_exists( $key ) ) {
			require plugin_dir_path( __FILE__ ) . 'inc/' . $value;
		}
	}

	$xliff_export = new Wpml_Xliff_Export();
	$xliff_export->setup();
}

function mlp_and_wp_loaded_handler( Inpsyde_Property_List_Interface $mlp_data ) {

	global $wpdb;
	$data = new Inpsyde_Property_List;

	$load_rule = new Inpsyde_Directory_Load( __DIR__ . '/inc' );
	$mlp_data->loader->add_rule( $load_rule );

	$wpml2mlp = new Wpml2mlp_Importer( $data, $wpdb );
	$wpml2mlp->setup();
}




