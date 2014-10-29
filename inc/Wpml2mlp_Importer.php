<?php

class Wpml2mlp_Importer {

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
	private static $show_success_msg;

	/**
	 * Constructor
	 *
	 * @param Inpsyde_Property_List_Interface $data
	 * @param wpdb                            $wpdb
	 */
	public function __construct( wpdb $wpdb = NULL ) {

		if ( NULL === $wpdb ) {
			return;
		}

		$this->wpdb = $wpdb;

		$link_table = $wpdb->base_prefix . 'multilingual_linked';

		$site_relations            = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
		$content_relations         = new Mlp_Content_Relations(
			$this->wpdb,
			$site_relations,
			$link_table
		);
		$this->site_creator        = new Wpml2mlp_Site_Creator( $this->wpdb );
		$this->post_creator        = new Wpml2mlp_Post_Creator( $this->wpdb, $content_relations );
		$this->main_language       = Wpml2mlp_Helper::get_main_language();
		$this->translation_builder = new Wpml2mlp_Translations_Builder( $this->main_language );
		$this->language_holder     = new Wpml2mlp_Language_Holder();

		self::$show_success_msg = FALSE;
	}

	/**
	 * Runs import of wmpl to the mlp and creates new sites in multisite environment if doesn't exists.
	 */
	private function do_wpml2mlp() {

		$lng_arr = icl_get_languages( 'skip_missing=1' );

		foreach ( $lng_arr as $lng ) {

			if ( ! $this->site_creator->site_exists( $lng ) ) {
				$this->site_creator->create_site( $lng );
			}
		}

		$this->blog_cache = FALSE; // reset object cache after adding new site. (it will be recreated)

		foreach ( Wpml2mlp_Helper::get_all_posts() as $current_post ) {

			$relevant_blog = $this->get_relevant_blog( $current_post );

			if ( $relevant_blog != FALSE && ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
				$mlp_post_id = $this->post_creator->add_post( $current_post, $relevant_blog );
			}
		}
		self::$show_success_msg = TRUE;
	}

	/**
	 * Runs the import from WPML to MLP
	 */
	public static function display() {

		?>
		<div class="wrap">
			<h2><?php _e( 'WPML 2 MLP import' ); ?></h2>

			<p><?php _e( 'MLP import from WPML.' ); ?></p>
			<?php
			if ( is_network_admin() ) {
				?>

				<form method="post" action="settings.php?page=wpml2mlp">

					<input type="hidden" name="post_type" value="do_wmpl_2_mlp" />

					<?php
					submit_button( __( 'Run WPML to MLP import' ) ); ?>

				</form>
				<?php
				if ( self::$show_success_msg ) {
					?>
					<p>
						You have successfully import WPML data to the MLP.
					</p>

				<?php
				}
			} else {
				?>
				<div>
					<button disabled="disabled">Run WPML to MLP import</button>
				</div><br />
				<div>
					To perform import from WPML to multi site you need to be<br /> on network admin and have "Multilingual Press" plugin enabled.
				</div>
			<?php } ?>
			</p>
			<hr />
		</div>
	<?php
	}

	/**
	 * Runs the import from WPML to MLP
	 */
	public function run_import() {

		if ( isset( $_POST[ 'submit' ] ) ) {

			if ( $_POST[ 'post_type' ] == 'do_wmpl_2_mlp' ) {
				$this->do_wpml2mlp();
			}
		}
	}

	/**
	 * Gets blog relevant for provided post.
	 *
	 * @param $post
	 *
	 * @return blog
	 */
	private function get_relevant_blog( $post ) {

		if ( ! $this->blog_cache ) {
			$this->blog_cache = wp_get_sites();
		}

		$pst_lng = Wpml2mlp_Helper::get_language_info( $post->ID );

		foreach ( $this->blog_cache as $ab ) {
			if ( get_blog_language( $ab[ 'blog_id' ], TRUE ) == $pst_lng ) {
				return $ab;
			}
		}

		return FALSE;
	}

	/**
	 * Setups the importer.
	 */
	public function setup() {

		/**
		 * Run import on admin_init
		 */
		add_action( 'admin_init', array( $this, 'run_import' ) );
	}
}