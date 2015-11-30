<?php
/**
 * Plugin Name: WPML to MultilingualPress
 * Plugin URI:  https://github.com/inpsyde/wpml2mlp
 * Description: Get data from WPML export and immediately import in Multisite and MultilingualPress environment.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     0.0.1.2
 */

defined( 'ABSPATH' ) or die( 'No direct access!' );

# Load plugin
add_action( 'plugins_loaded', 'wpml2mlp_prerequisites' );

/**
 * At first check the prerequisites to chose the way of use
 *
 * Set the textdomain for this Plugin and a error code for the prerequisites
 * and run the prerequisites. If no prerequisite lets wp die.
 *
 * @wp-hook plugins_loaded
 *
 * @since 0.0.1.2
 *
 */
function wpml2mlp_prerequisites(){

	require plugin_dir_path( __FILE__ ) . 'inc/Wpml2mlp_Prerequisites.php';

	$txt_domain = 'wpml2mlp';
	$error_code = $txt_domain . '_prerequisites';

	$prerequisites = Wpml2mlp_Prerequisites::check_prerequisites( $txt_domain, $error_code );

	if( $prerequisites->errors ){

		#deactivate_plugins( plugin_basename( __FILE__ ) );

		wp_die( $prerequisites->errors[$error_code][0] );

	}

	require plugin_dir_path( __FILE__ ) . 'inc/Wpml2mlp_Load.php';

	$wpml2mlp = new Wpml2mlp_Load();
	$wpml2mlp->_load();

}