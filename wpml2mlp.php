<?php
/**
 * Plugin Name: WPML to MultilingualPress
 * Plugin URI:  https://github.com/inpsyde/wpml2mlp
 * Description: Get data from WPML export and immediately import in Multisite and MultilingualPress environment.
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com
 * Version:     1.0.0
 */

defined( 'ABSPATH' ) or die( 'No direct access!' );

add_action( 'wp_loaded', 'wpml2mlp_load' );

function wpml2mlp_load() {

	$class_mappings = array(
		'Wpml2mlp_Categorie_Creator'    => 'Wpml2mlp_Categorie_Creator.php',
		'Wpml2mlp_Helper'               => 'Wpml2mlp_Helper.php',
		'Wpml2mlp_Importer'             => 'Wpml2mlp_Importer.php',
		'Wpml2mlp_Language_Holder'      => 'Wpml2mlp_Language_Holder.php',
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

	if ( Wpml2mlp_Prerequisites::is_mlp_plugin_active() ) {

		global $wpdb;

		$w2m_import = new Wpml2mlp_Importer( $wpdb );
		$w2m_import->setup();
	}

	$xliff_export = new Wpml_Xliff_Export();
	$xliff_export->setup();

	wpml2mlp_add_hooks();
}

/**
 * Creates hooks for plugin.
 */
function wpml2mlp_add_hooks() {

	add_action( 'admin_init', 'wpml2mlp_page_init' );
	add_action( 'network_admin_menu', 'wpml2mpl_add_menu_option' );
	add_action( 'admin_menu', 'wpml2mlp_admin_menu' );
}

/**
 * Add option to admin menu.
 */
function wpml2mlp_admin_menu() {

	add_submenu_page(
		'tools.php',
		'Convert WPML to MLP',
		'WPML2MLP',
		'manage_options',
		'wpml2mlp',
		'wpml2mlp_show_import'
	);
}

/**
 * Add menu to to network navigation.
 */
function wpml2mpl_add_menu_option() {

	add_submenu_page(
		'settings.php',
		'Convert WPML to MLP',
		strtoupper( 'wpml2mlp' ),
		'manage_network_options',
		'wpml2mlp',
		'wpml2mlp_show_import'
	);

}

/**
 * Register, add settings and checks prerequisites of the plugin.
 */
function wpml2mlp_page_init() {

	//check  check_prerequisites
	Wpml2mlp_Prerequisites::check_prerequisites();

	register_setting(
		'export_option_group', // Option group
		'export_option_name'
	);

	add_settings_section(
		'setting_section_id', // ID
		'', // Title
		NULL, // Callback
		'export-setting-admin' // Page
	);
}

/**
 * Displays relevant HTML content for plugin.
 */
function wpml2mlp_show_import() {

	if ( Wpml2mlp_Prerequisites::is_mlp_plugin_active() ) {
		Wpml2mlp_Importer::display();
	}

	Wpml_Xliff_Export::display();
}
