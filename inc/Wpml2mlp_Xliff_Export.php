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
	 * @var string
	 */
	private $action;

	/**
	 * Constructs new Wpml_Xliff_Export instance.
	 */
	public function __construct() {

		$this->main_language = Wpml2mlp_Helper::get_main_language();

		$this->translation_builder = new Wpml2mlp_Translations_Builder( $this->main_language );
		$this->language_holder     = new Wpml2mlp_Language_Holder();
		$this->xliff_creator       = new Wpml2mlp_Xliff_Creator();

		#$this->xliff_creator->setup();
	}

	/**
	 * Performs xliff export.
	 */
	private function do_xliff_export() {

		if( $this->prepare_xliff_data() ){

			$this->xliff_creator->do_xliff_export();

		}

	}

	/**
	 *
	 */
	private function do_store_xliff() {

		if( $this->prepare_store_data() ){

			$this->xliff_creator->store_wxr_export();

		}

		#debug( 'do_xliff_export' );

	}

	/**
	 *
	 */
	private function prepare_xliff_data() {

		foreach ( Wpml2mlp_Helper::get_all_posts() as $current_post ) {
			$this->set_xliff_item( $current_post->ID, $current_post, $this->language_holder );
		}

		$data = $this->language_holder->get_all_items();

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$this->xliff_creator->contentForExport = $data;
			return true;
		}

	}


	/**
	 *
	 */
	private function prepare_store_data() {

		$posts = array();

		foreach ( Wpml2mlp_Helper::get_all_posts() as $current_lang => $lang_obj ) {

			foreach( $lang_obj['posts'] as $current_post ){

				$current_post->translations = $this->map_translation_ids_to_source( $current_lang, $current_post );

				$posts[ $current_lang ]['posts'][] = $current_post;

			}

			$posts[ $current_lang ]['category'] = $lang_obj['category'];
			$posts[ $current_lang ]['post_tag'] = $lang_obj['post_tag'];

			unset( $lang_obj['posts'] );
			unset( $lang_obj['category'] );
			unset( $lang_obj['post_tag'] );

			foreach( $lang_obj as $custom_item => $custom_item_value ){

				$posts[ $current_lang ]['custom_items'][ $custom_item ] = $lang_obj[ $custom_item ];

			}

		}

		if ( is_array( $posts ) && count( $posts ) > 0 ) {
			$this->xliff_creator->contentForExport = $posts;
			return true;
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

		#TODO check wat is $mlp_post_id or is it a fail or we need a nother function?
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
	 * Query and collect post ids of translation from a given post id
	 *
	 * @param $current_lang
	 * @param $post
	 *
	 * @return mixed
	 */
	private function map_translation_ids_to_source( $current_lang, $post ){

		foreach( wpml_get_active_languages_filter( 1 ) as $lang_code => $lang_data ){

			if( $current_lang != $lang_code ){

				$translation_id = wpml_object_id_filter( $post->ID, $post->post_type, false, $lang_code );

				if( ! empty( $translation_id ) ){

					$translations[ $lang_data['default_locale'] ] = $translation_id;

				}

			}

		}

		return $translations;

	}

	/**
	 * Setup xliff export.
	 */
	public function setup( $action = FALSE ) {

		$this->action = $action;

		if ( $this->action == 'multisite_not_installed' ) {

			$this->do_store_xliff();

		}

		/**
		 * Run import on admin_init
		 */
		add_action( 'admin_init', array( $this, 'run_import' ) );

	}
}