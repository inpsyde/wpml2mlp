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
 * Reqiure needed files and heck the prerequisites to chose the way of use
 *
 * Set the textdomain for this Plugin and a error code for the prerequisites
 * and run the prerequisites. If no prerequisite lets wp die.
 *
 * @wp-hook plugins_loaded
 *
 * @since   0.0.1.2
 *
 */
function wpml2mlp_prerequisites() {

	$class_mappings = array(
		'Wpml2mlp_Categorie_Creator'    => 'Wpml2mlp_Categorie_Creator.php',
		'Wpml2mlp_Helper'               => 'Wpml2mlp_Helper.php',
		'Wpml2mlp_Importer'             => 'Wpml2mlp_Importer.php',
		'Wpml2mlp_Language_Holder'      => 'Wpml2mlp_Language_Holder.php',
		'Wpml2mlp_Load'                 => 'Wpml2mlp_Load.php',
		'Wpml2mlp_Post_Creator'         => 'Wpml2mlp_Post_Creator.php',
		'Wpml2mlp_Prerequisites'        => 'Wpml2mlp_Prerequisites.php',
		'Wpml2mlp_Site_Creator'         => 'Wpml2mlp_Site_Creator.php',
		'Wpml2mlp_Translation_Item'     => 'Wpml2mlp_Translation_Item.php',
		'Wpml2mlp_Translations'         => 'Wpml2mlp_Translations.php',
		'Wpml2mlp_Translations_Builder' => 'Wpml2mlp_Translations_Builder.php',
		'Wpml2mlp_Xliff_Creator'        => 'Wpml2mlp_Xliff_Creator.php',
		'Wpml2mlp_ZipCreator'           => 'Wpml2mlp_ZipCreator.php',
		'Wpml_Xliff_Export'             => 'Wpml_Xliff_Export.php',
		'Wpml2mlp_Xliff_Extractor'      => 'Wpml2mlp_Xliff_Extractor.php'
	);

	foreach ( $class_mappings as $key => $value ) {

		if ( ! class_exists( $key ) ) {
			require plugin_dir_path( __FILE__ ) . 'inc/' . $value;
		}

	}

	$txt_domain = 'wpml2mlp';
	$error_code = $txt_domain . '_prerequisites';

	$prerequisites = Wpml2mlp_Prerequisites::check_prerequisites( $txt_domain, $error_code );

	if ( $prerequisites->errors ) {

		#deactivate_plugins( plugin_basename( __FILE__ ) );

		wp_die( $prerequisites->errors[ $error_code ][ 0 ] );

	}

	$wpml2mlp = new Wpml2mlp_Load();
	$wpml2mlp->_load();

}