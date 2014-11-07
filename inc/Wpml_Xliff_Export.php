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
	public static function display() {

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
	 *
	 * @wp-hook admin_init
	 */
	public function run_import() {

		if ( isset( $_POST[ 'submit' ] ) ) {
			if ( $_POST[ 'post_type' ] == 'do_xliff_export' ) {
				$this->do_xliff_export();
			}
		}
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
		 * Run import on admin_init
		 */
		add_action( 'admin_init', array( $this, 'run_import' ) );
	}
}