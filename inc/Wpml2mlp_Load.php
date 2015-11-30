<?php


class Wpml2mlp_Load {


	public static function _load() {

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
	private static function wpml2mlp_add_hooks() {

		add_action( 'admin_init', 'wpml2mlp_page_init' );
		add_action( 'network_admin_menu', 'wpml2mpl_add_menu_option' );
		add_action( 'admin_menu', 'wpml2mlp_admin_menu' );
	}

	/**
	 * Add option to admin menu.
	 */
	private static function wpml2mlp_admin_menu() {

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
	private static function wpml2mpl_add_menu_option() {

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
	private static function wpml2mlp_page_init() {

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
	private static function wpml2mlp_show_import() {

		if ( Wpml2mlp_Prerequisites::is_mlp_plugin_active() ) {
			Wpml2mlp_Importer::display();
		}

		Wpml_Xliff_Export::display();
	}

}