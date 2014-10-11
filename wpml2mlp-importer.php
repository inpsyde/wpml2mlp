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
	 * @var $site_creator
	 */
	private $site_creator;

	/**
	 * @var &post_creator
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

		$this->wpdb         = $wpdb;
		$this->site_creator = new MLP_Site_Creator( $this->wpdb );
		$this->post_creator = new MLP_Post_Creator();

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
			$all_posts = WPML2MLP_Helper::get_all_posts();

			while ( $all_posts->have_posts() ) : $all_posts->the_post();

				$ID           = get_the_ID();
				$postType     = get_post_type( $ID );
				$translations = icl_get_languages( 'skip_missing=1' );

				foreach ( $translations as $translation ) {
					$langCode = $translation[ 'language_code' ];

					// pass relevant data if needed
					if ( ! $this->site_creator->site_exists( $langCode ) ) {
						$this->site_creator->create_site( $langCode );
					}

					// pass relevant data if needed
					if ( ! $this->post_creator->post_exists( $translation ) ) {
						$this->post_creator->add_post( $translation );
					}
				}
			endwhile;
		}
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'WPML 2 MLP' ); ?></h2>

			<p><?php _e( 'Conversion from WPML to MLP.' ); ?></p>

			<form method="post" action="settings.php?page=<?php echo 'wpml2mlp'; ?>">
				<?php submit_button( __( 'Do export' ) ); ?>
			</form>
		</div>
	<?php
	}
}