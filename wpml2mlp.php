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

	private $sites;

	private $mlp_site_relations;

	function check_prerequisites() {

		$wp_version_check      = $this->check_wordpress_version();
		$wp_is_multisite_check = $this->check_is_multisite_enabled();
		$wpml_installed        = $this->is_wpmlplugin_active();

		if ( $wp_version_check || $wp_is_multisite_check || ! $wpml_installed ) {
			$plugin      = plugin_basename( __FILE__ );
			$plugin_data = get_plugin_data( __FILE__, FALSE );
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				if ( $wp_version_check ) {

					$msg = "'" . $plugin_data[ 'Name' ] . "' requires WordPress " . WPVERSION_CONST . " or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='" . admin_url(
						) . "'>WordPress admin</a>.";
				}
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

	function check_is_multisite_enabled() {

		return ! is_multisite() ? TRUE : FALSE;
	}

	function check_wordpress_version() {

		global $wp_version;

		return version_compare( $wp_version, WPVERSION_CONST, "<" ) ? TRUE : FALSE;
	}

	function is_wpmlplugin_active() {

		return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ? TRUE : FALSE;
	}

	function __construct() {

		global $wpdb;
		$this->sites              = wp_get_sites();
		$this->mlp_site_relations = new Mlp_Site_Relations( $wpdb, "mlp_site_relations" );

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
			$queryParams = array(
				'posts_per_page' => - 1,
				'post_type'      => get_post_types( array( 'public' => TRUE ), 'names', 'and' )
			);

			$conversionData = new WP_Query();
			$conversionData->query( $queryParams );
			$new_blog_ids = array();
                        $default_lng_id = -1;
                        
			if ( $conversionData->have_posts() ) {
				//store grouped data
				$data = array();

				while ( $conversionData->have_posts() ) : $conversionData->the_post();
                                        
					$ID           = get_the_ID();
					$postType     = get_post_type( $ID );
					$translations = icl_get_languages( 'skip_missing=1' );
                                        
					foreach ( $translations as $translation ) {
						$translate_ID = icl_object_id( $ID, $postType, FALSE, $translation[ 'language_code' ] );
						//Filter out languages that do not have translations
						if ( $translate_ID != "" ) {
							$langCode = $translation[ 'language_code' ];
                                                        
                                                        // check is this correnct language format
                                                        if ( ! self::site_exists( $langCode ) ) {
                                                            $new_blog_id = self::create_new_multisite( $langCode );
                                                            if ( $new_blog_id > 0 ) {
                                                                    array_push( $new_blog_ids, $new_blog_id );
                                                            }
                                                        }
                                                        
							if ( ! array_key_exists( $langCode, $data ) ) {
								$data[ $langCode ] = array();
							}
							if ( ! array_key_exists( "posts", $data[ $langCode ] ) ) {
								$data[ $langCode ][ 'posts' ] = array();
							}
							array_push( $data[ $langCode ][ 'posts' ], get_post( $translate_ID ) );

						}
						if ( $default_lng_id < 0 && $ID == $translate_ID ) {
                                                    $default_lng_id = $translation["id"];
                                                }
					}
				endwhile;
			} else {
				echo 'There is no any posts';

			}
			//var_dump( $data );
			//var_dump( $this->get_inpsyde_multilingual() );
                        
                        if( count($new_blog_ids) > 0 ) {
                            if($default_lng_id < 0) {
                                $default_lng_id = 1;
                            }
                            $this->mlp_site_relations->set_relation($default_lng_id, $new_blog_ids);
                        }
		}
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo 'WPML 2 MLP'; ?></h2>

			<p><?php echo 'Conversion from WPML to MLP.'; ?></p>

			<form method="post" action="settings.php?page=<?php echo Wpml2MlpConstants::PREFIX_CONST; ?>">
				<?php submit_button( 'Do export' ); ?>
			</form>
		</div>
	<?php
	}

	private function create_new_multisite( $lng, $main_site_id = 1 ) {

		$is_multisite_on_subdomain = self::check_is_subdomain_multisite_running();
		$current_site              = get_current_site();
		$domain                    = $is_multisite_on_subdomain ? $lng . $current_site->domain : $current_site->domain;
		$path                      = $is_multisite_on_subdomain ? "/" : "/" . $lng;
		$user_id                   = get_current_user_id();

		$new_blog_id = wpmu_create_blog( $domain, $path, strtoupper( $lng ) . " site", $user_id );

		if ( $new_blog_id > 0 ) {
			$site_meta                       = self::create_inpsyde_multilingual_site_meta( $lng, $new_blog_id );
			$mlp_site_option                 = self::get_inpsyde_multilingual_site_meta();
			$mlp_site_option[ $new_blog_id ] = $site_meta;
			self::update_inpsyde_multilingual_site_meta( $mlp_site_option );
		}

		return $new_blog_id;
	}
        
        private function site_exists( $lng ) {
            return true; // TODO: implement this
        }

	private function delete_multisite( $blog_id, $main_blog_id = 0 ) {

		wpmu_delete_blog( $blog_id, TRUE );
		$this->mlp_site_relations->delete_relation( $main_blog_id, $blog_id );
		$inpsyde_multilingual_site_option = self::get_inpsyde_multilingual_site_meta();
		if ( is_array( $inpsyde_multilingual_site_option )
			&& array_key_exists(
				$blog_id, $inpsyde_multilingual_site_option
			)
		) {
			unset( $inpsyde_multilingual_site_option[ $blog_id ] );
			self::update_inpsyde_multilingual_site_meta( $inpsyde_multilingual_site_option );
		}

	}

	private function create_inpsyde_multilingual_site_meta( $lng, $new_blog_id, $text = "" ) {

		return

			array(
				'lang' => $lng,
				'text' => $text,

			);
	}

	private function update_inpsyde_multilingual_site_meta( $site_meta_arr ) {

		update_site_option( "inpsyde_multilingual", serialize( $site_meta_arr ) );
	}

	private function get_inpsyde_multilingual_site_meta() {

		return maybe_unserialize( get_site_option( "inpsyde_multilingual" ) );
	}

	private function check_is_subdomain_multisite_running() {

		return defined( 'SUBDOMAIN_INSTALL' ) ? SUBDOMAIN_INSTALL : FALSE;
	}

	private function get_sites_ids() {

		$ids = array();

		foreach ( $this->sites as $site ) {
			array_push( $ids, $site[ blog_id ] );
		}
		sort( $ids, SORT_NUMERIC );

		return $ids;
	}

	private function site_id_exist( $id ) {

		return FALSE;
	}

	private function try_get_last_multisite_id( &$last_id ) {

		$ids   = self::get_sites_ids();
		$count = count( $ids );
		if ( count( $count ) > 0 ) {
			$last_id = $ids[ $count - 1 ];

			return TRUE;
		}

		return FALSE;
	}
}

//init plugin
add_action(
	"plugins_loaded", function () {

		global $wpml_2_mlp;
		$wpml_2_mlp = new Wpml_2_Mlp();
	}
);