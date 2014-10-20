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

add_filter( 'plugins_loaded', array( 'WPML2MLP_Importer', 'get_object' ) );

add_action(
	'mlp_and_wp_loaded',
	function ( Inpsyde_Property_List_Interface $mlp_data ) {

		$load_rule = new Inpsyde_Directory_Load( __DIR__ . '/inc' );
		$mlp_data->loader->add_rule( $load_rule );
	}
);

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
	 * @var WPML2MLP_Site_Creator
	 */
	private $site_creator;

	/**
	 * @var WPML2MLP_Post_Creator
	 */
	private $post_creator;

	/**
	 * @var WPML2MLP_Xliff_Creator
	 */
	private $xliff_creator;

	/**
	 * @var WPML2MLP_Translations_Builder
	 */
	private $translation_builder;

	/**
	 * @var WPML2MLP_Language_Holder
	 */
	private $language_holder;

	/**
	 * @var string
	 */
	private $main_language;

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
		$this->site_creator        = new WPML2MLP_Site_Creator( $this->wpdb );
		$this->post_creator        = new WPML2MLP_Post_Creator( $this->wpdb, $content_relations );
		$this->xliff_creator       = new WPML2MLP_Xliff_Creator();
		$this->main_language       = WPML2MLP_Helper::get_main_language();
		$this->translation_builder = new WPML2MLP_Translations_Builder( $this->main_language );
		$this->language_holder     = new WPML2MLP_Language_Holder();

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

			foreach ( WPML2MLP_Helper::get_all_posts() as $current_post ) {

				$relevant_blog = $this->get_relevant_blog( $current_post );

				if ( $relevant_blog != FALSE && ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
					$mlp_post_id = $this->post_creator->add_post( $current_post, $relevant_blog );

					if ( $do_xliff_export && $mlp_post_id ) {
						$this->set_xliff_item( $mlp_post_id, $current_post, $this->language_holder );
					}
				}
			}

			if ( $do_xliff_export ) {
				do_action( 'WPML2MLP_xliff_export', $this->language_holder->get_all_items() );
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

		$pst_lng = WPML2MLP_Helper::get_language_info( $post->ID );

		foreach ( $this->blog_cache as $ab ) {
			if ( get_blog_language( $ab[ 'blog_id' ], TRUE ) == $pst_lng ) {
				return $ab;
			}
		}

		return FALSE;
	}

	private function set_xliff_item( $mlp_post_id, $post, WPML2MLP_Language_Holder &$language_holder ) {

		$post_lang = WPML2MLP_Helper::get_language_info( $post->ID );

		if ( $post_lang != $this->main_language ) { // don't map default language
			$post_translations = $this->translation_builder->build_translation_item( $post, $mlp_post_id );

			if ( $post_translations ) {
				foreach ( $post_translations as $trans_item ) {
					$language_holder->set_item( $trans_item, $this->main_language, $post_lang );
				}
			}
		}
	}
}