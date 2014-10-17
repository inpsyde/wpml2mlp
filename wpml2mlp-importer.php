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

$class_mappings = array(
	'MLP_Site_Creator'         => 'mlp-site-creator.php',
	'MLP_Post_Creator'         => 'mlp-post-creator.php',
	'WPML2MLP_Helper'          => 'wpml2mlp-Helper.php',
	'MLP_Xliff_Creator'        => 'mlp-xliff-creator.php',
	'ZipCreator'               => 'zip-creator.php',
	'MLP_Translation_Item'     => 'mlp-translation-item.php',
	'MLP_Language_Holder'      => 'mlp-language-holder.php',
	'MLP_Translations_Builder' => 'mlp-translations-builder.php'
);

foreach ( $class_mappings as $key => $value ) {
	if ( ! class_exists( $key ) ) {
		require plugin_dir_path( __FILE__ ) . 'inc/' . $value;
	}
}

add_filter( 'plugins_loaded', array( 'WPML2MLP_Importer', 'get_object' ) );

class WPML2MLP_Importer {

	/**
	 * The class object
	 *
	 * @since  0.0.1
	 * @var    String
	 */
	static protected $class_object = NULL;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var blog_cache
	 */

	private $blog_cache;

	/**
	 * @var MLP_Site_Creator
	 */
	private $site_creator;

	/**
	 * @var MLP_Post_Creator
	 */
	private $post_creator;

	/**
	 * @var MLP_Xliff_Creator
	 */
	private $xliff_creator;

	/**
	 * @var MLP_Translations_Builder
	 */
	private $translation_builder;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		global $wpdb;

		if ( NULL === $wpdb ) {
			return;
		}

		$link_table                = $wpdb->base_prefix . 'multilingual_linked';
		$this->wpdb                = $wpdb;
		$site_relations            = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
		$content_relations         = new Mlp_Content_Relations(
			$this->wpdb,
			$site_relations,
			$link_table
		);
		$this->site_creator        = new MLP_Site_Creator( $this->wpdb );
		$this->post_creator        = new MLP_Post_Creator( $this->wpdb, $content_relations );
		$this->xliff_creator       = new MLP_Xliff_Creator();
		$this->translation_builder = new MLP_Translations_Builder( WPML2MLP_Helper::get_main_language() );

		// add menu to to network navigation
		add_action( "network_admin_menu", array( $this, "add_menu_option" ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

	}

	/**
	 * Adds the menu option to the settings
	 */
	public function add_menu_option() {

		add_submenu_page(
			'settings.php',
			'Convert WPML to MLP',
			strtoupper( 'wpml2mlp' ),
			'manage_network_options',
			'wpml2mlp',
			array( $this, 'run_import' )
		);

	}

	/**
	 * Load the object and get the current state
	 *
	 * @since   0.0.1
	 * @return String $class_object
	 */
	public static function get_object() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}

		return self::$class_object;
	}

	/**
	 * Runs the import from WPML to MLP
	 */
	public function run_import() {
		
		if ( isset( $_POST[ 'submit' ] ) ) {

			$lng_arr = icl_get_languages( 'skip_missing=1' );

			foreach ( $lng_arr as $lng ) {

				if ( ! $this->site_creator->site_exists( $lng ) ) {
					$this->site_creator->create_site( $lng );
				}
			}

			$this->blog_cache = FALSE; // reset object cache after adding new site. (it will be recreated)

			$do_xliff_export = isset( $_POST[ 'exporttofile' ] ) && $_POST[ 'exporttofile' ] == "1";
			$language_holder = new MLP_Language_Holder();
			foreach ( WPML2MLP_Helper::get_all_posts() as $current_post ) {

				$relevant_blog = $this->get_relevant_blog( $current_post );

				if ( $relevant_blog != FALSE && ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
					$this->post_creator->add_post( $current_post, $relevant_blog );

					if ( $do_xliff_export ) {
						$post_translations = $this->translation_builder->build_translation_item( $current_post );
						if ( $post_translations ) {
							foreach ( $post_translations as $trans_item ) {
								$language_holder->setItem( $trans_item );
							}
						}
					}
				}
			}

			if ( $do_xliff_export ) {
				$this->xliff_creator->do_xliff_export( $language_holder->getAllItems() );
			}

			?>

			<div class="wrap">
				You have successfully import WPML data to the MLP.
			</div><?php
		}
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'WPML 2 MLP' ); ?></h2>

			<p><?php _e( 'Conversion from WPML to MLP.' ); ?></p>

			<form method="post" action="settings.php?page=<?php echo 'wpml2mlp'; ?>">

				<?php
				// This prints out all hidden setting fields
				settings_fields( 'export_option_group' );
				do_settings_sections( 'export-setting-admin' );
				submit_button( __( 'Run WPML to MLP import' ) ); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {

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

		add_settings_field(
			'id_export', // ID
			'Export XLIFF?', // Title
			array( $this, 'id_export_callback' ), // Callback
			'export-setting-admin', // Page
			'setting_section_id' // Section
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function id_export_callback() {

		//$options = get_option( 'plugin_options' );
		echo "<label><input checked=checked  value='0' name='exporttofile' type='radio' /> No</label><br />";
		echo "<label><input value='1' name='exporttofile' type='radio' /> Yes</label><br />";

	}

	private function get_relevant_blog( $post ) {

		if ( ! $this->blog_cache ) {
			$this->blog_cache = wp_get_sites();
		}

		$pst_lng = wpml_get_language_information( $post->ID );

		foreach ( $this->blog_cache as $ab ) {
			if ( get_blog_language( $ab[ 'blog_id' ], TRUE ) == WPML2MLP_Helper::get_short_language(
					$pst_lng[ 'locale' ]
				)
			) {
				return $ab;
			}
		}

		return FALSE;
	}
}