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

defined( 'ABSPATH' ) or die( "No direct access!" );

add_action( 'mlp_and_wp_loaded', 'mlp_and_wp_loaded_handler' );

function mlp_and_wp_loaded_handler ( Inpsyde_Property_List_Interface $mlp_data ) {

	global $wpdb;
	$data = new Inpsyde_Property_List;

	$load_rule = new Inpsyde_Directory_Load( __DIR__ . '/inc' );
	$mlp_data->loader->add_rule( $load_rule );

	$wpml2mlp = new Wpml2mlp_Importer( $data, $wpdb );
	$wpml2mlp->setup();
}




