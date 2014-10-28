<?php

/**
 * Class Wpml_Xliff_Export
 */
class Wpml_Xliff_Export {

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
	 * Constructs new Wpml_Xliff_Export instance.
	 */
	public function __construct() {

		$this->main_language = Wpml2mlp_Helper::get_main_language();

		$this->translation_builder = new Wpml2mlp_Translations_Builder( $this->main_language );
		$this->language_holder     = new Wpml2mlp_Language_Holder();
		$this->xliff_creator       = new Wpml2mlp_Xliff_Creator();
		$this->xliff_creator->setup();
	}

	/**
	 * Performs xliff export.
	 */
	private function do_xliff_export() {

		foreach ( Wpml2mlp_Helper::get_all_posts() as $current_post ) {
			$this->set_xliff_item( $current_post->ID, $current_post, $this->language_holder );
		}

		$data = $this->language_holder->get_all_items();

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$this->xliff_creator->contentForExport = $data;
			$this->xliff_creator->do_xliff_export();
		}
	}

	/**
	 * Shows the Xliff export
	 */
	public function show_import() {

		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'WPML 2 XLIFF export' ); ?></h2>

			<p><?php _e( 'Xliff export from WPML.' ); ?></p>

			<p>

			<form method="post" action="<?php echo ! is_network_admin() ? 'tools'
				: 'settings' ?>.php?page=wpml2mlp">
				<input type="hidden" name="post_type" value="do_xliff_export" />
				<?php
				submit_button( __( 'Run translations export to xliff' ) ); ?>
			</form>
			</p>
		</div>
	<?php
	}

	/**
	 * Runs the import from WPML to MLP
	 */
	public function run_import() {

		if ( isset( $_POST[ 'submit' ] ) ) {

			$this->do_xliff_export();
		}
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function id_export_callback() {

		//$options = get_option( 'plugin_options' );
		echo "<label><input checked=checked  value='0' name='exporttofile' type='radio' /> No</label><br />";
		echo "<label><input value='1' name='exporttofile' type='radio' /> Yes</label><br />";

	}

	/**
	 * Set xliff item.
	 *
	 * @param                          $mlp_post_id
	 * @param                          $post
	 * @param WPML2MLP_Language_Holder $language_holder
	 */
	private function set_xliff_item( $mlp_post_id, $post, WPML2MLP_Language_Holder &$language_holder ) {

		$post_lang = Wpml2mlp_Helper::get_language_info( $post->ID );

		if ( $post_lang != $this->main_language ) { // don't map default language
			$post_translations = $this->translation_builder->build_translation_item( $post, $mlp_post_id );

			if ( $post_translations ) {
				foreach ( $post_translations as $trans_item ) {
					$language_holder->set_item( $trans_item, $this->main_language, $post_lang );
				}
			}
		}
	}

	/**
	 * Setup xliff export.
	 */
	public function setup() {

		/**
		 * Add menu to to network navigation
		 */
		add_action( "network_admin_menu", array( $this, "add_menu_option" ) );
		add_action( "admin_menu", array( $this, "wpml_admin_menu" ) );
		/**
		 * Check plugin check_prerequisites
		 */
		add_action( 'admin_init', array( $this, 'page_init' ) );
		/**
		 * Run import on admin_init
		 */
		add_action( 'admin_init', array( $this, 'run_import' ) );
	}

	/**
	 * Wpml admin menu.
	 */
	function wpml_admin_menu() {

		add_submenu_page(
			'tools.php',
			'Convert WPML to MLP',
			'WPML2MLP',
			'manage_options',
			'wpml2mlp',
			array( $this, 'show_import' )
		);
	}

	/**
	 * Adds wpml2mlp menu option.
	 */
	public function add_menu_option() {

		add_submenu_page(
			'settings.php',
			'Convert WPML to MLP',
			strtoupper( 'wpml2mlp' ),
			'manage_network_options',
			'wpml2mlp',
			array( $this, 'show_import' )
		);

	}

	/**
	 * Register and add settings
	 */
	public function page_init() {

		//check  check_prerequisites
		//Wpml2mlp_Prerequisites::check_prerequisites();

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
}