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

if ( ! class_exists( 'Wpml2MlpConstants' ) ) {
	require plugin_dir_path( __FILE__ ) . 'constants.php';
}

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

		// add menu to to network navigation
		add_action( "network_admin_menu", array( &$this, "add_menu_option" ) );

	}

	// Add menu page
	function add_menu_option() {

		add_submenu_page(
			'settings.php',
			Wpml2MlpConstants::CONVERT_WPML_TO_MLP,
			strtoupper( Wpml2MlpConstants::PREFIX_CONST ),
			'manage_network_options',
			Wpml2MlpConstants::PREFIX_CONST,
			array( &$this, 'options_page' )
		);

	}

	// Options Form
	function options_page() {

		if ( isset( $_POST[ 'submit' ] ) ) {
			echo( "Exporting..." );
		}
		?>
		<div class="wrap">

			<!-- Display Plugin Icon, Header, and Description -->
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo 'WPML 2 MLP'; ?></h2>

			<p><?php echo 'Conversion from WPML to MLP.'; ?></p>


			<!-- Beginning of the Plugin Options Form -->
			<form method="post" action="settings.php?page=<?php echo Wpml2MlpConstants::PREFIX_CONST; ?>">


				<?php submit_button( 'Do export' ); ?>


			</form>


		</div>
	<?php
	}
}

//init plugin
add_action( "plugins_loaded", "wpml_2_mlp_init" );

function wpml_2_mlp_init() {

	global $wpml_2_mlp;
	$wpml_2_mlp = new Wpml_2_Mlp();
}