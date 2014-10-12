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
	'MLP_Site_Creator' => 'mlp-site-creator.php',
	'MLP_Post_Creator' => 'mlp-post-creator.php',
	'WPML2MLP_Helper'  => 'wpml2mlp-Helper.php'
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
	 * @var MLP_Site_Creator
	 */
	private $site_creator;

	/**
	 * @var MLP_Post_Creator
	 */
	private $post_creator;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {

		global $wpdb;

		if ( NULL === $wpdb ) {
			return;
		}

		$link_table         = $wpdb->base_prefix . 'multilingual_linked';
		$this->wpdb         = $wpdb;
		$site_relations     = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
		$content_relations  = new Mlp_Content_Relations(
			$this->wpdb,
			$site_relations,
			$link_table
		);
		$this->site_creator = new MLP_Site_Creator( $this->wpdb, $site_relations, $content_relations );
		$this->post_creator = new MLP_Post_Creator( $this->wpdb, $content_relations );

		// add menu to to network navigation
		add_action( "network_admin_menu", array( $this, "add_menu_option" ) );
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
			$current_site = get_current_site();
			$lng_arr      = icl_get_languages( 'skip_missing=1' );

			foreach ( $lng_arr as $lng ) {
				if ( $current_site->id == $lng[ 'id' ] ) { // check is default language set in MLP
					$this->site_creator->check_and_update_site_lagnguage(
						$current_site->blog_id,
						$lng[ 'default_locale' ]
					);
				}

				if ( ! $this->site_creator->site_exists( $lng ) ) {
					$this->site_creator->create_site( $lng );
				}
			}

			$this->blog_cache = FALSE; // reset object cache after adding new site. (it will be recreated)

			foreach ( WPML2MLP_Helper::get_all_posts() as $current_post ) {
				$relevant_blog = $this->get_relevant_blog( $current_post );

				if ( ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
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
				<?php submit_button( __( 'Run WPML to MLP import' ) ); ?>
			</form>
		</div>
	<?php
	}

	private $blog_cache;

	private function get_relevant_blog( $post ) {

		if ( ! $this->blog_cache ) {
			$this->blog_cache = get_blog_list();
		}

		$pst_lng = wpml_get_language_information( $post->ID );

		foreach ( $this->blog_cache as $ab ) {
			if ( get_blog_language( $ab[ 'blog_id' ], FALSE ) == $pst_lng[ 'locale' ] ) {
				return $ab;
			}
		}

		return FALSE;
	}
}