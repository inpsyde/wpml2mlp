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
	'MLP_Site_Creator'  => 'mlp-site-creator.php',
	'MLP_Post_Creator'  => 'mlp-post-creator.php',
	'WPML2MLP_Helper'   => 'wpml2mlp-Helper.php',
	'MLP_Xliff_Creator' => 'mlp-xliff-creator.php',
	'ZipCreator'        => 'zip-creator.php',

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
	 * Constructor
	 *
	 */
	public function __construct() {

		global $wpdb;

		if ( NULL === $wpdb ) {
			return;
		}

		$link_table          = $wpdb->base_prefix . 'multilingual_linked';
		$this->wpdb          = $wpdb;
		$site_relations      = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
		$content_relations   = new Mlp_Content_Relations(
			$this->wpdb,
			$site_relations,
			$link_table
		);
		$this->site_creator  = new MLP_Site_Creator( $this->wpdb );
		$this->post_creator  = new MLP_Post_Creator( $this->wpdb, $content_relations );
		$this->xliff_creator = new MLP_Xliff_Creator();

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
		/*$translator = new LanguageHolder();

		$translator->setItem(new TranslationItem("bla", "blaaaa", "de_DE"));
		$translator->setItem(new TranslationItem("alb", "albbbb", "it_IT"));
		$translator->setItem(new TranslationItem("alsfdfb", "sdfsafdsaf", "it_IT"));

		var_dump($translator->getAllItems());*/


		/*foreach ( WPML2MLP_Helper::get_all_posts() as $current_post ) {

			$relevant_blog = $this->get_relevant_blog( $current_post );

			var_dump( $current_post );
		}*/

		if ( isset( $_POST[ 'submit' ] ) ) {
			//Todo move this to correct locatio and pass correct data
			if ( isset( $_POST[ 'exporttofile' ] ) && $_POST[ 'exporttofile' ] == "1" ) {
				do_action( 'WPML2MLP_xliff_export', $_POST ); //pass post translations instead of post obj
			}

			$lng_arr = icl_get_languages( 'skip_missing=1' );

			foreach ( $lng_arr as $lng ) {

				if ( ! $this->site_creator->site_exists( $lng ) ) {
					$this->site_creator->create_site( $lng );
				}
			}

			$this->blog_cache = FALSE; // reset object cache after adding new site. (it will be recreated)

			foreach ( WPML2MLP_Helper::get_all_posts() as $current_post ) {

				$relevant_blog = $this->get_relevant_blog( $current_post );

				if ( $relevant_blog != FALSE && ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
					$this->post_creator->add_post( $current_post, $relevant_blog );
				}
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


class TranslationItem {
	private $source;
	private $destination;
	private $language;

	public function __construct($source, $destination, $language) {
		$this->source = $source;
		$this->destination = $destination;
		$this->language = $language;
	}

	public function getSource(){
		return $this->source;
	}

	public function  getDestination(){
		return $this->destination;
	}

	public function getLanguage(){
		return $this->language;
	}

	public function isValid(){
		return !empty($this->source) && !empty($this->destination) && !empty($this->language);
	}
}
interface iHoldTranslations {

	public function setItem(TranslationItem &$translationItem);

	public function getAllItems();
}

class LanguageHolder implements iHoldTranslations {
	private $mapper;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$mapper = array();
	}

	public function setItem(TranslationItem &$translationItem) {
		if(!$translationItem->isValid()){
			return;
		}

		$lng = $translationItem->getLanguage();
		$this->checkLanguage($lng);

		array_push($this->mapper[$lng], $translationItem);
	}

	public function getAllItems() {
		return array_values($this->mapper);
	}

	private function checkLanguage($language) {
		if($this->mapper == NULL) {
			$this->mapper = array();
		}
		if ( ! array_key_exists($language, $this->mapper)) {
			$this->mapper[$language] = array();
		}
	}
}